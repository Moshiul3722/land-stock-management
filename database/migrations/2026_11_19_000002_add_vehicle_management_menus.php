<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sidebar module "Vehicle Management" with its six pages, the new role "Vehicle Driver",
 * and who may open what:
 *   - Super Admin and Admin: every vehicle page
 *   - Vehicle Driver: the demand form only
 * The rest can be changed later on the "Menu Permission" page.
 *
 * Data only: inserts rows into cnf_module, cnf_submodule, cnf_menu, cnf_role and
 * cnf_permission. Nothing existing is modified.
 */
return new class extends Migration
{
    private const MODULE_EN = 'Vehicle Management';

    public const DRIVER_ROLE_EN = 'Vehicle Driver';

    /** route => [English, Bangla, sort order] */
    private const MENUS = [
        'vehicle-types.index' => ['Vehicle Types', 'যানবাহনের ধরন', 10],
        'vehicle-officers.index' => ['Officers', 'কর্মকর্তা', 20],
        'vehicle-drivers.index' => ['Vehicle Drivers', 'যানবাহনের চালক', 30],
        'vehicle-routes.index' => ['Vehicle Routes', 'যানবাহনের রুট', 40],
        'vehicles.index' => ['Vehicle Details', 'যানবাহনের বিবরণ', 50],
        'vehicle-demands.index' => ['Vehicle Demand Form', 'যানবাহনের চাহিদা ফরম', 60],
    ];

    public function up(): void
    {
        if (DB::table('cnf_module')->where('module_name_en', self::MODULE_EN)->whereNull('deleted_at')->exists()) {
            return;
        }

        DB::transaction(function () {
            $now = now();
            $audit = ['created_at' => $now, 'updated_at' => $now, 'created_by' => 1, 'updated_by' => 1];

            $moduleId = DB::table('cnf_module')->insertGetId([
                'module_name_en' => self::MODULE_EN,
                'module_name_bn' => 'যানবাহন ব্যবস্থাপনা',
                'is_active' => 1,
            ] + $audit);

            $submoduleId = DB::table('cnf_submodule')->insertGetId([
                'submodule_name_en' => self::MODULE_EN,
                'submodule_name_bn' => 'যানবাহন ব্যবস্থাপনা',
                'module_id' => $moduleId,
                'is_active' => 1,
                'is_null' => 0,
            ] + $audit);

            $menuIds = [];
            foreach (self::MENUS as $route => [$nameEn, $nameBn, $sort]) {
                $menuIds[$route] = DB::table('cnf_menu')->insertGetId([
                    'submodule_id' => $submoduleId,
                    'menu_name_en' => $nameEn,
                    'menu_name_bn' => $nameBn,
                    'controller' => 'Vehicle',
                    'action' => 'View',
                    'route' => $route,
                    'is_menu' => '1',
                    'sort_order' => $sort,
                    'is_active' => 1,
                ] + $audit);
            }

            $driverRoleId = DB::table('cnf_role')->where('role_name_en', self::DRIVER_ROLE_EN)->whereNull('deleted_at')->value('id')
                ?? DB::table('cnf_role')->insertGetId([
                    'role_name_en' => self::DRIVER_ROLE_EN,
                    'role_name_bn' => 'যানবাহন চালক',
                ] + $audit);

            $grants = [];
            foreach ([1, 2] as $roleId) {
                foreach ($menuIds as $menuId) {
                    $grants[] = [$roleId, $menuId];
                }
            }
            $grants[] = [$driverRoleId, $menuIds['vehicle-demands.index']];

            foreach ($grants as [$roleId, $menuId]) {
                DB::table('cnf_permission')->insert(['role_id' => $roleId, 'menu_id' => $menuId] + $audit);
            }
        });
    }

    public function down(): void
    {
        $menuIds = DB::table('cnf_menu')->whereIn('route', array_keys(self::MENUS))->pluck('id');
        DB::table('cnf_permission')->whereIn('menu_id', $menuIds)->delete();
        $submoduleIds = DB::table('cnf_menu')->whereIn('id', $menuIds)->distinct()->pluck('submodule_id');
        DB::table('cnf_menu')->whereIn('id', $menuIds)->delete();
        $moduleIds = DB::table('cnf_submodule')->whereIn('id', $submoduleIds)->distinct()->pluck('module_id');
        DB::table('cnf_submodule')->whereIn('id', $submoduleIds)->delete();
        DB::table('cnf_module')->whereIn('id', $moduleIds)->delete();
        // The driver role stays when users still have it
        $roleId = DB::table('cnf_role')->where('role_name_en', self::DRIVER_ROLE_EN)->value('id');
        if ($roleId && ! DB::table('users')->where('role_id', $roleId)->exists()) {
            DB::table('cnf_permission')->where('role_id', $roleId)->delete();
            DB::table('cnf_role')->where('id', $roleId)->delete();
        }
    }
};
