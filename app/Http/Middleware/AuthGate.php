<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthGate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $route = $request->route()->getName();
        // Get the authenticated user
        $user = Auth::user();
        // Check if the user has the necessary permission
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        if (!$this->hasPermission($user->role_id, $route)) {

            // Get the route path from which the request is being generated
            $referrerUrl = $request->server('HTTP_REFERER');
            // Parse the URL to extract the route path
            $referrerRoutePath = parse_url($referrerUrl, PHP_URL_PATH);
            // Trimming left and right
            $referrerRoutePath = ltrim($referrerRoutePath, '/');
            $referrerRoutePath = rtrim($referrerRoutePath, '/');
            // Get all routes
            $routes = Route::getRoutes();

            foreach ($routes as $singleRoute) {
                $routeUri = $singleRoute->uri();

                // Construct the pattern to match the route URI with the given route URL
                $pattern = preg_replace('/\{(\w+)(\?)?\}/', '(?P<$1>\d+)$2', $routeUri);

                // Ensure optional parameters are handled correctly
                $pattern = str_replace(['?}', '})'], ')?', $pattern);

                if (preg_match('~^' . $pattern . '$~', $referrerRoutePath) && $referrerRoutePath != 'home') {
                    return $next($request);
                }
            }
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
    private function hasPermission($roleId, $route)
    {

        // Step 1: Query the cnf_permission table to retrieve menu IDs associated with the role
        $menuIds = DB::table('cnf_permission')
            ->where('role_id', $roleId)
            ->pluck('menu_id')
            ->toArray();

        // Step 2: Query the cnf_menu table to get the routes associated with the retrieved menu IDs
        $routes = DB::table('cnf_menu')
            ->whereIn('id', $menuIds)
            ->pluck('route')
            ->toArray();

        // Step 3: Check if the given route matches any of the routes obtained from the database
        return in_array($route, $routes);
    }
}
