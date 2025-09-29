<?php

namespace App\Repositories;

use App\Models\CnfPermission;
use App\Repositories\BaseRepository;
use App\Models\CnfMenu;
use App\Models\CnfSubmodule;
use App\Models\CnfModule;

class CnfPermissionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'role_id',
        'menu_id',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CnfPermission::class;
    }

    public function getPermissionsWithMenusAndSubmodules($roleId)
    {
        // Retrieve all menus, submodules, and modules
        $menus = CnfMenu::with('submodule.module')->get();

        // Retrieve permissions for the specific role
        $rolePermissions = CnfPermission::where('role_id', $roleId)->pluck('menu_id')->toArray();

        $menuItems = [];
        foreach ($menus as $menu) {
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
                'menu_id' => $menu->id,
            ];

            // Check if the menu has permission for the role
            $hasPermission = in_array($menu->id, $rolePermissions) ? 1 : 0;
            $menuItem['has_permission'] = $hasPermission;

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
