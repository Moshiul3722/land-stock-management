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
        return $this->routesByRole[$roleId] ??= DB::table('cnf_permission')
            ->join('cnf_menu', 'cnf_menu.id', '=', 'cnf_permission.menu_id')
            ->where('cnf_permission.role_id', $roleId)
            ->pluck('cnf_menu.route')
            ->all();
    }
}
