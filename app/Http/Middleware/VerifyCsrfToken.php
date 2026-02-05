<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // API routes that should return JSON on CSRF failure
            $apiRoutes = ['save-pattern', 'save-fingerprint', 'branch/switch'];
            $apiRouteNames = ['save.pattern', 'save.fingerprint', 'branch.switch'];
            
            $isApiRoute = false;
            
            // Check by URL path
            foreach ($apiRoutes as $route) {
                if ($request->is($route) || $request->is('*/' . $route)) {
                    $isApiRoute = true;
                    break;
                }
            }
            
            // Check by route name
            if (!$isApiRoute && $request->route()) {
                $routeName = $request->route()->getName();
                if (in_array($routeName, $apiRouteNames)) {
                    $isApiRoute = true;
                }
            }
            
            // Return JSON for API routes
            if ($isApiRoute || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSRF token mismatch. Please refresh the page and try again.',
                    'error' => 'TokenMismatchException'
                ], 419)->header('Content-Type', 'application/json');
            }
            
            throw $e;
        }
    }
}
