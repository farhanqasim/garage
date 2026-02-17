<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Handle authentication exceptions - redirect to login
        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.', 'redirect' => route('login')], 401);
            }
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        });
        
        // Handle general errors with authentication issues
        $this->renderable(function (\ErrorException $e, $request) {
            // Check if error is related to null user/auth
            if (str_contains($e->getMessage(), 'Attempt to read property') && str_contains($e->getMessage(), 'on null')) {
                // Check if it's an auth-related error
                if (!auth()->check()) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Authentication required.', 'redirect' => route('login')], 401);
                    }
                    return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
                }
            }
        });
    }
    
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle API routes that should ALWAYS return JSON for POST requests
        $apiRoutes = ['branch/switch', 'save-pattern', 'save-fingerprint'];
        $apiRouteNames = ['branch.switch', 'save.pattern', 'save.fingerprint'];
        $isApiRoute = false;
        
        // Get current path and URI (normalize by removing query string and trailing slashes)
        $currentPath = trim($request->path(), '/');
        $currentUri = $request->getRequestUri();
        $currentUrl = $request->url();
        
        // Check by URL path (more flexible matching)
        foreach ($apiRoutes as $route) {
            $normalizedRoute = trim($route, '/');
            
            // Exact match on path
            if ($currentPath === $normalizedRoute || $currentPath === $route) {
                $isApiRoute = true;
                break;
            }
            // Check URI (includes query string)
            if (str_contains($currentUri, $normalizedRoute) || str_contains($currentUri, $route)) {
                $isApiRoute = true;
                break;
            }
            // Ends with route
            if (str_ends_with($currentPath, $normalizedRoute) || str_ends_with($currentPath, $route)) {
                $isApiRoute = true;
                break;
            }
            // Contains route in path
            if (str_contains($currentPath, $normalizedRoute) || str_contains($currentPath, $route)) {
                $isApiRoute = true;
                break;
            }
        }
        
        // Also check if request has X-Requested-With header (AJAX indicator)
        if (!$isApiRoute && $request->header('X-Requested-With') === 'XMLHttpRequest') {
            foreach ($apiRoutes as $route) {
                if (str_contains($currentPath, $route) || str_contains($currentUri, $route)) {
                    $isApiRoute = true;
                    break;
                }
            }
        }
        
        // Check by route name
        if (!$isApiRoute && $request->route()) {
            $routeName = $request->route()->getName();
            if ($routeName && in_array($routeName, $apiRouteNames)) {
                $isApiRoute = true;
            }
        }
        
        // Also check if request expects JSON
        if (!$isApiRoute && ($request->expectsJson() || $request->wantsJson())) {
            // Check if it's one of our API routes by path
            foreach ($apiRoutes as $route) {
                if (str_contains($currentPath, $route)) {
                    $isApiRoute = true;
                    break;
                }
            }
        }
        
        // Check if request is AJAX/JSON for branch/switch route
        $isAjaxRequest = $request->ajax() || 
                        $request->wantsJson() || 
                        $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                        str_contains($request->header('Accept', ''), 'application/json');
        
        if (($isApiRoute || $isAjaxRequest) && $request->isMethod('POST')) {
            // Always return JSON for API POST requests
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $exception->errors()
                ], 422)->header('Content-Type', 'application/json');
            }
            
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401)->header('Content-Type', 'application/json');
            }
            
            // Handle CSRF token mismatch
            if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSRF token mismatch. Please refresh the page and try again.',
                    'error' => 'TokenMismatchException'
                ], 419)->header('Content-Type', 'application/json');
            }
            
            // Handle HTTP exceptions (404, 500, etc.)
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $statusCode = $exception->getStatusCode();
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'HTTP Error ' . $statusCode,
                    'error' => 'HttpException',
                    'status_code' => $statusCode
                ], $statusCode)->header('Content-Type', 'application/json');
            }
            
            // For any other exception in API routes, return JSON
            $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'An error occurred',
                'error' => get_class($exception),
                'debug' => config('app.debug') ? [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ] : null
            ], $statusCode)->header('Content-Type', 'application/json');
        }
        
        // Handle WebAuthn routes - always return JSON
        if ($request->is('webauthn/*')) {
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $exception->errors()
                ], 422);
            }
            
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            // For any other exception in WebAuthn routes, return JSON
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'An error occurred',
                'error' => config('app.debug') ? $exception->getTraceAsString() : null
            ], 500);
        }
        
        // Handle CSRF token mismatch (419 Page Expired) - redirect with message instead of raw 419 page
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            if (auth()->check()) {
                return redirect()->back()->withInput($request->except('password', '_token'))
                    ->with('error', 'Your session has expired. Please refresh the page and try again.');
            }
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        }

        // Handle authentication errors
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.', 'redirect' => route('login')], 401);
            }
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        }
        
        // Handle null user errors
        if ($exception instanceof \ErrorException) {
            if (str_contains($exception->getMessage(), 'Attempt to read property') && 
                (str_contains($exception->getMessage(), 'user') || str_contains($exception->getMessage(), 'auth'))) {
                if (!auth()->check()) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Authentication required.', 'redirect' => route('login')], 401);
                    }
                    return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
                }
            }
        }

        return parent::render($request, $exception);
    }
}
