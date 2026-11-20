<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vehicle management menu: new page "Fuel Usage" (জ্বালানি খরচের হিসাব), after "Fuel Demand",
 * for Super Admin and Admin (can be changed later on the "Menu Permission" page).
 *
 * Data only: inserts one cnf_menu row and its cnf_permission rows.
 */
return new class extends Migration
{
    private const ROUTE = 'vehicle-fuel-report.index';

    public function up(): void
    {
        DB::transaction(function () {
            $fuel = DB::table('cnf_menu')->where('route', 'vehicle-demands.index')->first();
            if (! $fuel || DB::table('cnf_menu')->where('route', self::ROUTE)->exists()) {
                return;
            }
            $now = now();
            $audit = ['created_at' => $now, 'updated_at' => $now, 'created_by' => 1, 'updated_by' => 1];
            $menuId = DB::table('cnf_menu')->insertGetId([
                'submodule_id' => $fuel->submodule_id,
                'menu_name_en' => 'Fuel Usage',
                'menu_name_bn' => 'জ্বালানি খরচের হিসাব',
                'controller' => 'Vehicle',
                'action' => 'View',
                'route' => self::ROUTE,
                'is_menu' => '1',
                'sort_order' => 70,
                'is_active' => 1,
            ] + $audit);

            foreach ([1, 2] as $roleId) {
                DB::table('cnf_permission')->insert(['role_id' => $roleId, 'menu_id' => $menuId] + $audit);
            }
        });
    }

    public function down(): void
    {
        $menuIds = DB::table('cnf_menu')->where('route', self::ROUTE)->pluck('id');
        DB::table('cnf_permission')->whereIn('menu_id', $menuIds)->delete();
        DB::table('cnf_menu')->whereIn('id', $menuIds)->delete();
    }
};
