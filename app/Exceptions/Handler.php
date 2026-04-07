<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // 1. Handle OOM errors
        $msg = $exception->getMessage();
        if (str_contains($msg, 'Allowed memory size') || (str_contains($msg, 'memory') && str_contains($msg, 'exhausted'))) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request used too much memory. Try narrowing your filters.',
                ], 509);
            }

            return response(
                '<html><body><h1>Service Unavailable</h1><p>This request used too much memory. Try narrowing your filters.</p><p><a href="javascript:history.back()">Go Back</a></p></body></html>',
                509
            )->header('Content-Type', 'text/html');
        }

        // 3. Handle database connection errors (e.g. MySQL stopped)
        $isDbError = $exception instanceof \Illuminate\Database\QueryException
            || $exception instanceof \PDOException
            || (str_contains($msg, 'SQLSTATE') && (str_contains($msg, '2002') || str_contains($msg, '1045') || str_contains($msg, 'Connection refused') || str_contains($msg, 'No connection could be made')));
        if ($isDbError && ! config('app.debug')) {
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => 'Database unavailable. Please ensure MySQL is running in XAMPP.'], 503);
            }

            return response()->view('errors.db-unavailable', [], 503);
        }

        // 4. Determine if request expects JSON
        $wantsJson = $request->expectsJson()
            || $request->ajax()
            || $request->is('webauthn/*')
            || $request->is('api/*')
            || $request->is('car-wash/api/*')
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->header('Accept', ''), 'application/json');

        // 3. Handle authentication errors
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // 4. Handle CSRF token mismatch
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please refresh the page and try again.',
                ], 419);
            }
            if (auth()->check()) {
                return redirect()->back()
                    ->withInput($request->except('password', '_token'))
                    ->with('error', 'Your session expired. Please try again.');
            }

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // 5. Handle null user/auth errors (session expired mid-request)
        if ($exception instanceof \ErrorException
            && str_contains($exception->getMessage(), 'Attempt to read property')
            && str_contains($exception->getMessage(), 'on null')
            && ! auth()->check()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // 6. For JSON/AJAX requests, return JSON for any exception (so frontend always gets parseable error)
        if ($wantsJson) {
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                $errors = $exception->errors();

                return response()->json([
                    'success' => false,
                    'message' => collect($errors)->flatten()->first() ?: 'Validation failed',
                    'errors' => $errors,
                ], 422);
            }

            $statusCode = method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : 500;

            $message = $exception->getMessage();
            if ($statusCode === 500 && ! config('app.debug')) {
                $message = $message ?: 'Something went wrong. Please try again.';
                // Avoid exposing technical messages to users in production
                if (str_contains($message, 'SQLSTATE') || str_contains($message, 'exception') || strlen($message) > 200) {
                    $message = 'Something went wrong. Please try again.';
                }
            }
            $message = $message ?: 'An error occurred. Please try again.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $statusCode);
        }

        return parent::render($request, $exception);
    }
}
