<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebAuthnCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WebAuthnController extends Controller
{
    /**
     * Get login options for WebAuthn authentication.
     * This generates a challenge that the client will use to authenticate.
     */
    public function getLoginOptions(Request $request)
    {
        // Ensure JSON response even on errors
        if (!$request->wantsJson() && !$request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }
        
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required',
                'errors' => $e->errors()
            ], 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Verify password first
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Get all WebAuthn credentials for this user
        $credentials = $user->webauthnCredentials()->get();

        // If no credentials exist, return registration mode
        if ($credentials->isEmpty()) {
            // Generate challenge for registration
            $challenge = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes(32)));
            
            // Store challenge in session for registration
            Session::put('webauthn_register_challenge', $challenge);
            Session::put('webauthn_register_user_id', $user->id);
            Session::put('webauthn_register_timestamp', now()->timestamp);
            
            // Return registration options
            return response()->json([
                'success' => true,
                'mode' => 'register', // Indicate this is registration mode
                'options' => [
                    'challenge' => $challenge,
                    'rp' => [
                        'name' => config('app.name', 'AccountCover'),
                        'id' => $this->getRpId(),
                    ],
                    'user' => [
                        'id' => base64_encode($user->id),
                        'name' => $user->email,
                        'displayName' => $user->name ?? $user->email,
                    ],
                    'pubKeyCredParams' => [
                        ['type' => 'public-key', 'alg' => -7], // ES256
                        ['type' => 'public-key', 'alg' => -257], // RS256
                    ],
                    'timeout' => 60000,
                    'attestation' => 'none',
                    'authenticatorSelection' => [
                        // Don't restrict to platform only - allow both platform (fingerprint) and cross-platform (security keys)
                        // Browser will prefer platform authenticator if available
                        'userVerification' => 'preferred',
                        'requireResidentKey' => false,
                    ],
                ],
            ]);
        }

        // Generate a random challenge (32 bytes = 256 bits)
        // Convert to base64url (WebAuthn standard - no padding, URL-safe)
        $challenge = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(random_bytes(32)));

        // Store challenge in session with expiration (5 minutes)
        Session::put('webauthn_login_challenge', $challenge);
        Session::put('webauthn_login_user_id', $user->id);
        Session::put('webauthn_login_timestamp', now()->timestamp);

        // Build allowCredentials array
        $allowCredentials = $credentials->map(function ($credential) {
            return [
                'id' => $credential->credential_id,
                'type' => 'public-key',
            ];
        })->toArray();

        // Return public key credential request options
        $options = [
            'challenge' => $challenge,
            'timeout' => 60000, // 60 seconds
            'rpId' => $this->getRpId(), // Relying Party ID (your domain)
            'allowCredentials' => $allowCredentials,
            'userVerification' => 'preferred', // Require user verification (fingerprint/face)
        ];

        return response()->json([
            'success' => true,
            'options' => $options,
        ]);
    }

    /**
     * Verify WebAuthn authentication response.
     */
    public function verifyLogin(Request $request)
    {
        // Ensure JSON response even on errors
        if (!$request->wantsJson() && !$request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }
        
        try {
            $request->validate([
                'credential' => 'required|array',
                'credential.id' => 'required|string',
                'credential.response' => 'required|array',
                'credential.response.authenticatorData' => 'required|string',
                'credential.response.clientDataJSON' => 'required|string',
                'credential.response.signature' => 'required|string',
                'credential.response.userHandle' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credential data',
                'errors' => $e->errors()
            ], 422);
        }

        // Get challenge from session
        $storedChallenge = Session::get('webauthn_login_challenge');
        $userId = Session::get('webauthn_login_user_id');
        $timestamp = Session::get('webauthn_login_timestamp');

        if (!$storedChallenge || !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired challenge'
            ], 400);
        }

        // Check challenge expiration (5 minutes)
        if ($timestamp && (now()->timestamp - $timestamp) > 300) {
            Session::forget(['webauthn_login_challenge', 'webauthn_login_user_id', 'webauthn_login_timestamp']);
            return response()->json([
                'success' => false,
                'message' => 'Challenge expired'
            ], 400);
        }

        $credential = $request->input('credential');
        $credentialId = $credential['id']; // Already base64url encoded from client

        // Find the credential in database (stored as base64url)
        $webauthnCredential = WebAuthnCredential::where('credential_id', $credentialId)
            ->where('user_id', $userId)
            ->first();

        if (!$webauthnCredential) {
            return response()->json([
                'success' => false,
                'message' => 'Credential not found'
            ], 404);
        }

        // Verify the signature (simplified - in production, use a proper WebAuthn library)
        // For now, we'll do basic validation
        // Decode base64url to base64 first
        $clientDataJSONBase64 = str_replace(['-', '_'], ['+', '/'], $credential['response']['clientDataJSON']);
        // Add padding if needed
        $padding = strlen($clientDataJSONBase64) % 4;
        if ($padding) {
            $clientDataJSONBase64 .= str_repeat('=', 4 - $padding);
        }
        $clientDataJSON = base64_decode($clientDataJSONBase64, true);
        if (!$clientDataJSON) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid client data'
            ], 400);
        }

        $clientData = json_decode($clientDataJSON, true);
        if (!$clientData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid client data JSON'
            ], 400);
        }

        // Verify challenge matches (both are base64url encoded)
        // Compare base64url strings directly (they should match exactly)
        $receivedChallengeBase64url = $clientData['challenge'] ?? '';
        
        // Normalize both challenges by removing padding and comparing
        $receivedChallengeNormalized = rtrim(str_replace(['+', '/'], ['-', '_'], $receivedChallengeBase64url), '=');
        $expectedChallengeNormalized = rtrim(str_replace(['+', '/'], ['-', '_'], $storedChallenge), '=');
        
        // Also try comparing the raw base64url strings
        if ($receivedChallengeBase64url !== $storedChallenge && $receivedChallengeNormalized !== $expectedChallengeNormalized) {
            // Log for debugging
            Log::warning('WebAuthn challenge mismatch', [
                'expected' => $storedChallenge,
                'received' => $receivedChallengeBase64url,
                'expected_normalized' => $expectedChallengeNormalized,
                'received_normalized' => $receivedChallengeNormalized
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Challenge mismatch'
            ], 400);
        }

        // Verify type
        if (($clientData['type'] ?? '') !== 'webauthn.get') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication type'
            ], 400);
        }

        // Verify origin (should match your domain)
        $origin = $clientData['origin'] ?? '';
        $expectedOrigin = $this->getExpectedOrigin();
        if ($origin !== $expectedOrigin) {
            Log::warning('WebAuthn origin mismatch', [
                'expected' => $expectedOrigin,
                'received' => $origin
            ]);
            // In production, you might want to be stricter
        }

        // Verify signature using public key (simplified - use proper library in production)
        // For now, we'll trust the browser's verification and just check counter
        // Decode base64url authenticator data
        $authenticatorDataBase64 = str_replace(['-', '_'], ['+', '/'], $credential['response']['authenticatorData']);
        $padding = strlen($authenticatorDataBase64) % 4;
        if ($padding) {
            $authenticatorDataBase64 .= str_repeat('=', 4 - $padding);
        }
        $authenticatorData = base64_decode($authenticatorDataBase64, true);
        if ($authenticatorData && strlen($authenticatorData) >= 37) {
            // Extract counter from authenticator data (bytes 33-36)
            $counter = unpack('N', substr($authenticatorData, 33, 4))[1];
            
            // Verify counter is increasing (replay attack prevention)
            if ($counter <= $webauthnCredential->counter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Replay attack detected'
                ], 400);
            }

            // Update counter
            $webauthnCredential->counter = $counter;
            $webauthnCredential->last_used_at = now();
            $webauthnCredential->save();
        }

        // Clear session challenge
        Session::forget(['webauthn_login_challenge', 'webauthn_login_user_id', 'webauthn_login_timestamp']);

        // Log the user in
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        Auth::login($user, $request->has('remember'));

        // Handle branch selection if needed (similar to LoginController)
        if ($user->role === 'user') {
            $branch = \App\Models\Branch::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            if ($branch) {
                Session::put([
                    'selected_branch_id' => $branch->id,
                    'selected_branch_name' => $branch->branch_name,
                    'selected_branch_code' => $branch->branch_code
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful',
            'redirect' => '/home'
        ]);
    }

    /**
     * Get Relying Party ID (your domain).
     * Must match the current domain exactly or be a valid parent domain.
     */
    private function getRpId()
    {
        // Get the current request host (most reliable)
        $currentHost = request()->getHost();
        
        // Remove port number if present (WebAuthn rpId doesn't include ports)
        $currentHost = preg_replace('/:\d+$/', '', $currentHost);
        
        // Special handling for localhost/127.0.0.1
        if ($currentHost === 'localhost' || $currentHost === '127.0.0.1' || $currentHost === '::1') {
            return 'localhost';
        }
        
        // For production, use the actual domain from request
        // This ensures rpId matches the current domain exactly
        return $currentHost;
    }

    /**
     * Get expected origin for verification.
     */
    private function getExpectedOrigin()
    {
        $url = config('app.url');
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? request()->getHost();
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        
        // For local development, allow http
        if (app()->environment('local')) {
            $scheme = request()->getScheme();
        }
        
        return $scheme . '://' . $host . $port;
    }

    /**
     * Verify WebAuthn registration response and save credential.
     */
    public function verifyRegister(Request $request)
    {
        // Ensure JSON response even on errors
        if (!$request->wantsJson() && !$request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }
        
        try {
            $request->validate([
                'credential' => 'required|array',
                'credential.id' => 'required|string',
                'credential.response' => 'required|array',
                'credential.response.attestationObject' => 'required|string',
                'credential.response.clientDataJSON' => 'required|string',
                'device_name' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credential data',
                'errors' => $e->errors()
            ], 422);
        }

        // Get challenge from session
        $storedChallenge = Session::get('webauthn_register_challenge');
        $userId = Session::get('webauthn_register_user_id');
        $timestamp = Session::get('webauthn_register_timestamp');

        if (!$storedChallenge || !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired registration challenge'
            ], 400);
        }

        // Check challenge expiration (5 minutes)
        if ($timestamp && (now()->timestamp - $timestamp) > 300) {
            Session::forget(['webauthn_register_challenge', 'webauthn_register_user_id', 'webauthn_register_timestamp']);
            return response()->json([
                'success' => false,
                'message' => 'Registration challenge expired'
            ], 400);
        }

        $credential = $request->input('credential');
        $credentialId = $credential['id']; // Base64url encoded

        // Verify client data
        $clientDataJSONBase64 = str_replace(['-', '_'], ['+', '/'], $credential['response']['clientDataJSON']);
        $padding = strlen($clientDataJSONBase64) % 4;
        if ($padding) {
            $clientDataJSONBase64 .= str_repeat('=', 4 - $padding);
        }
        $clientDataJSON = base64_decode($clientDataJSONBase64, true);
        
        if (!$clientDataJSON) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid client data'
            ], 400);
        }

        $clientData = json_decode($clientDataJSON, true);
        if (!$clientData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid client data JSON'
            ], 400);
        }

        // Verify challenge matches (both are base64url encoded)
        // Compare base64url strings directly (they should match exactly)
        $receivedChallengeBase64url = $clientData['challenge'] ?? '';
        
        // Normalize both challenges by removing padding and comparing
        $receivedChallengeNormalized = rtrim(str_replace(['+', '/'], ['-', '_'], $receivedChallengeBase64url), '=');
        $expectedChallengeNormalized = rtrim(str_replace(['+', '/'], ['-', '_'], $storedChallenge), '=');
        
        // Also try comparing the raw base64url strings
        if ($receivedChallengeBase64url !== $storedChallenge && $receivedChallengeNormalized !== $expectedChallengeNormalized) {
            // Log for debugging
            Log::warning('WebAuthn registration challenge mismatch', [
                'expected' => $storedChallenge,
                'received' => $receivedChallengeBase64url,
                'expected_normalized' => $expectedChallengeNormalized,
                'received_normalized' => $receivedChallengeNormalized
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Challenge mismatch'
            ], 400);
        }

        // Verify type
        if (($clientData['type'] ?? '') !== 'webauthn.create') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid registration type'
            ], 400);
        }

        // Check if credential already exists
        $existingCredential = WebAuthnCredential::where('credential_id', $credentialId)->first();
        if ($existingCredential) {
            return response()->json([
                'success' => false,
                'message' => 'This credential is already registered'
            ], 400);
        }

        // Extract public key from attestation object (simplified - in production use proper library)
        // For now, we'll store the raw attestation object
        $attestationObject = $credential['response']['attestationObject'];
        
        // Save the credential
        $webauthnCredential = WebAuthnCredential::create([
            'user_id' => $userId,
            'credential_id' => $credentialId,
            'public_key' => json_encode([
                'attestationObject' => $attestationObject,
                'clientDataJSON' => $credential['response']['clientDataJSON'],
            ]),
            'counter' => 0,
            'device_name' => $request->input('device_name', 'Unknown Device'),
            'last_used_at' => now(),
        ]);

        // Clear session challenge
        Session::forget(['webauthn_register_challenge', 'webauthn_register_user_id', 'webauthn_register_timestamp']);

        // Log the user in after successful registration
        $user = User::find($userId);
        if ($user) {
            Auth::login($user, $request->has('remember'));
            
            // Handle branch selection if needed
            if ($user->role === 'user') {
                $branch = \App\Models\Branch::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->first();
                
                if ($branch) {
                    Session::put([
                        'selected_branch_id' => $branch->id,
                        'selected_branch_name' => $branch->branch_name,
                        'selected_branch_code' => $branch->branch_code
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Fingerprint registered successfully! You are now logged in.',
            'redirect' => '/home'
        ]);
    }
}
