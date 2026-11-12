<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\CnfPermission;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        // Pass the permissions to the view composer
        View::composer('layouts.menu', function ($view) {
            // Retrieve the authenticated user's role ID
            $roleId = Auth::user()->role_id;

            // Retrieve permissions with associated menus, submodules, and modules
            $permissions = CnfPermission::where('role_id', $roleId)
                ->with('menu.submodule.module')
                ->get();

            // Process the permissions data here and pass it to the view
            $menuItems = $this->processMenuItems($permissions);
            $view->with('menuItems', $menuItems);
        });

        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $service = App::make(NotificationService::class);
                $view->with('unreadCount', $service->getUnreadCount(Auth::id()));
                $view->with('recentNotifications', $service->getRecentUnread(Auth::id(), 5)); // Limit 5
            } else {
                $view->with('unreadCount', 0);
                $view->with('recentNotifications', collect([]));
            }
        });
    }

    private function processMenuItems($permissions)
    {
        $menuItems = [];
        foreach ($permissions as $permission) {
            $menu = $permission->menu;
            $submodule = $menu->submodule;
            $module = $submodule->module;

            // Determine the locale
            $locale = app()->getLocale();

            // Construct menu item structure
            $menuItem = [
                'module' => $locale === 'bn' ? $module->module_name_bn : $module->module_name_en,
                'submodule' => $locale === 'bn' ? $submodule->submodule_name_bn : $submodule->submodule_name_en,
                'menu_name' => $locale === 'bn' ? $menu->menu_name_bn : $menu->menu_name_en,
                'route' => $menu->route,
                'is_menu' => $menu->is_menu,
                'active' => Request::is($menu->route . '*') ? 'active' : '',
                // Position inside its submodule: an explicit sort_order, else the menu id.
                'sort' => $menu->sort_order ?? $menu->id * 10,
            ];

            // Group menu items by module, submodule
            if ($locale == 'bn') {
                $menuItems[$module->module_name_bn][$submodule->submodule_name_bn][] = $menuItem;
            } else {
                $menuItems[$module->module_name_en][$submodule->submodule_name_en][] = $menuItem;
            }
        }

        // Order the menus inside each submodule (modules and submodules keep their order).
        foreach ($menuItems as &$submodules) {
            foreach ($submodules as &$menus) {
                usort($menus, fn($first, $second) => $first['sort'] <=> $second['sort']);
            }
            unset($menus);
        }
        unset($submodules);

        return $menuItems;
    }
}
