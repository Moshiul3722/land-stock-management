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

        // Listed the way the sidebar is built: module by module, group by group ("Product
        // Management" below "Product Unit"), the pages shown in the sidebar first and the
        // pages reached from them (create, edit, print ...) after.
        $groupOrder = fn ($submoduleId) => (int) $submoduleId === 4 ? 11.5 : (float) $submoduleId;
        $menus = $menus->sort(function ($first, $second) use ($groupOrder) {
            return [$first->submodule->module_id, $groupOrder($first->submodule_id), -(int) $first->is_menu, $first->sort_order ?? $first->id * 10, $first->id]
                <=> [$second->submodule->module_id, $groupOrder($second->submodule_id), -(int) $second->is_menu, $second->sort_order ?? $second->id * 10, $second->id];
        })->values();

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

            // The requisition module has no heading in the sidebar: its groups ("Requisition
            // Management", "My Products") stand at the top level, so they do here as well.
            if ((int) $module->id === 4) {
                $menuItem['module'] = $menuItem['submodule'];
                $menuItem['submodule'] = '—';
            }

            // Check if the menu has permission for the role
            $hasPermission = in_array($menu->id, $rolePermissions) ? 1 : 0;
            $menuItem['has_permission'] = $hasPermission;

            // Group menu items by module, submodule
            $menuItems[$menuItem['module']][$menuItem['submodule']][] = $menuItem;
        }
        return $menuItems;
    }
}
