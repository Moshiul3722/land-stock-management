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
        // Allowed when the role was granted this page on the "Menu Permission" page, or
        // the route belongs to a page it was granted (saving, deleting, approving,
        // printing ...), or it is open to every signed-in user. Which route belongs to
        // which page: config/menu_access.php.
        if (! app(\App\Services\MenuPermissionService::class)->allowsRoute($user, $route)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
