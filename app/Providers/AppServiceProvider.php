<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;
use App\Models\CnfMenu;
use App\Models\CnfPermission;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

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
            ];

            // Group menu items by module, submodule
            if ($locale == 'bn') {
                $menuItems[$module->module_name_bn][$submodule->submodule_name_bn][] = $menuItem;
            } else {
                $menuItems[$module->module_name_en][$submodule->submodule_name_en][] = $menuItem;
            }
        }
        return $menuItems;
    }
}
