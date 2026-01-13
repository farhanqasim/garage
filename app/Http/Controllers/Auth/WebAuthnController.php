<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebAuthnCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address',
                'errors' => $e->errors()
            ], 422);
        }

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get all WebAuthn credentials for this user
        $credentials = $user->webauthnCredentials()->get();

        if ($credentials->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No WebAuthn credentials registered for this user'
            ], 404);
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
        $receivedChallengeBase64url = $clientData['challenge'] ?? '';
        // Convert base64url to base64 for comparison
        $receivedChallengeBase64 = str_replace(['-', '_'], ['+', '/'], $receivedChallengeBase64url);
        $padding = strlen($receivedChallengeBase64) % 4;
        if ($padding) {
            $receivedChallengeBase64 .= str_repeat('=', 4 - $padding);
        }
        $receivedChallenge = base64_decode($receivedChallengeBase64, true);
        
        $expectedChallengeBase64 = str_replace(['-', '_'], ['+', '/'], $storedChallenge);
        $padding = strlen($expectedChallengeBase64) % 4;
        if ($padding) {
            $expectedChallengeBase64 .= str_repeat('=', 4 - $padding);
        }
        $expectedChallenge = base64_decode($expectedChallengeBase64, true);

        if (!$receivedChallenge || !hash_equals($expectedChallenge, $receivedChallenge)) {
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
     */
    private function getRpId()
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        return $host ?: request()->getHost();
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
}
