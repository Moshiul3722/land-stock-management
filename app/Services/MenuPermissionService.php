<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Checks whether a user's role is granted a menu route in cnf_permission / cnf_menu.
 *
 * This is the same rule the `permission` middleware (App\Http\Middleware\AuthGate)
 * applies to page requests. Livewire actions arrive on /livewire/update, which the
 * middleware does not guard, so components call this service before every action.
 */
class MenuPermissionService
{
    /** @var array<int, array<int, string>> Routes per role, cached for the request. */
    private array $routesByRole = [];

    public function allows(?User $user, string $routeName): bool
    {
        if (! $user || ! $user->role_id) {
            return false;
        }

        return in_array($routeName, $this->routesFor((int) $user->role_id), true);
    }

    /**
     * @return array<int, string>
     */
    private function routesFor(int $roleId): array
    {
        // A permission taken away on the "Menu Permission" page is soft-deleted, so
        // only rows that are not deleted count.
        return $this->routesByRole[$roleId] ??= DB::table('cnf_permission')
            ->join('cnf_menu', 'cnf_menu.id', '=', 'cnf_permission.menu_id')
            ->where('cnf_permission.role_id', $roleId)
            ->whereNull('cnf_permission.deleted_at')
            ->whereNull('cnf_menu.deleted_at')
            ->pluck('cnf_menu.route')
            ->all();
    }

    /**
     * Whether the user's role may call a route behind the `permission` middleware:
     * a page granted on the "Menu Permission" page, a route that belongs to such a
     * page, or one open to every signed-in user (see config/menu_access.php).
     */
    public function allowsRoute(?User $user, ?string $routeName): bool
    {
        if (! $user || ! $user->role_id || ! $routeName) {
            return false;
        }

        if (in_array($routeName, (array) config('menu_access.open', []), true)) {
            return true;
        }

        if ($this->allows($user, $routeName)) {
            return true;
        }

        foreach ((array) config('menu_access.parents', []) as $pattern => $menuRoutes) {
            $matches = str_ends_with($pattern, '.*')
                ? str_starts_with($routeName, substr($pattern, 0, -1))
                : $pattern === $routeName;
            if (! $matches) {
                continue;
            }
            foreach ($menuRoutes as $menuRoute) {
                if ($this->allows($user, $menuRoute)) {
                    return true;
                }
            }
        }

        return false;
    }
}
