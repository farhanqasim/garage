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
                return response()->json(['message' => 'Unauthenticated.', 'redirect' => url('/')], 401);
            }
            return redirect('/')->with('error', 'Your session has expired. Please login again.');
        });
        
        // Handle general errors with authentication issues
        $this->renderable(function (\ErrorException $e, $request) {
            // Check if error is related to null user/auth
            if (str_contains($e->getMessage(), 'Attempt to read property') && str_contains($e->getMessage(), 'on null')) {
                // Check if it's an auth-related error
                if (!auth()->check()) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Authentication required.', 'redirect' => url('/')], 401);
                    }
                    return redirect('/')->with('error', 'Your session has expired. Please login again.');
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
        // Handle authentication errors
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.', 'redirect' => url('/')], 401);
            }
            return redirect('/')->with('error', 'Your session has expired. Please login again.');
        }
        
        // Handle null user errors
        if ($exception instanceof \ErrorException) {
            if (str_contains($exception->getMessage(), 'Attempt to read property') && 
                (str_contains($exception->getMessage(), 'user') || str_contains($exception->getMessage(), 'auth'))) {
                if (!auth()->check()) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Authentication required.', 'redirect' => url('/')], 401);
                    }
                    return redirect('/')->with('error', 'Your session has expired. Please login again.');
                }
            }
        }

        return parent::render($request, $exception);
    }
}
