<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vehicle management menus:
 *   - the driver's form "Vehicle Demand Form" is renamed "Fuel Demand" (জ্বালানির চাহিদা)
 *   - new page "Vehicle Demand" (যানবাহনের চাহিদা, the vehicle requisition form), placed
 *     before it and given to every role except "Vehicle Driver"
 *
 * Data only: renames one cnf_menu row, inserts one cnf_menu row and its cnf_permission rows.
 */
return new class extends Migration
{
    private const ROUTE = 'vehicle-requisitions.index';

    public function up(): void
    {
        DB::transaction(function () {
            $fuel = DB::table('cnf_menu')->where('route', 'vehicle-demands.index')->first();
            if (! $fuel) {
                return;
            }
            DB::table('cnf_menu')->where('id', $fuel->id)->update(['menu_name_en' => 'Fuel Demand', 'menu_name_bn' => 'জ্বালানির চাহিদা']);

            if (DB::table('cnf_menu')->where('route', self::ROUTE)->exists()) {
                return;
            }
            $now = now();
            $audit = ['created_at' => $now, 'updated_at' => $now, 'created_by' => 1, 'updated_by' => 1];
            $menuId = DB::table('cnf_menu')->insertGetId([
                'submodule_id' => $fuel->submodule_id,
                'menu_name_en' => 'Vehicle Demand',
                'menu_name_bn' => 'যানবাহনের চাহিদা',
                'controller' => 'Vehicle',
                'action' => 'View',
                'route' => self::ROUTE,
                'is_menu' => '1',
                'sort_order' => 55,
                'is_active' => 1,
            ] + $audit);

            $roleIds = DB::table('cnf_role')->whereNull('deleted_at')->where('role_name_en', '!=', 'Vehicle Driver')->pluck('id');
            foreach ($roleIds as $roleId) {
                DB::table('cnf_permission')->insert(['role_id' => $roleId, 'menu_id' => $menuId] + $audit);
            }
        });
    }

    public function down(): void
    {
        $menuIds = DB::table('cnf_menu')->where('route', self::ROUTE)->pluck('id');
        DB::table('cnf_permission')->whereIn('menu_id', $menuIds)->delete();
        DB::table('cnf_menu')->whereIn('id', $menuIds)->delete();
        DB::table('cnf_menu')->where('route', 'vehicle-demands.index')->update(['menu_name_en' => 'Vehicle Demand Form', 'menu_name_bn' => 'যানবাহনের চাহিদা ফরম']);
    }
};
