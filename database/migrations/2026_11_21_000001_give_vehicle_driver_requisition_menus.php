<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "Vehicle Driver" role gets the requisition list, like a General User: the list itself,
 * creating a requisition and printing its slip. Can be changed later on the "Menu Permission"
 * page.
 *
 * Data only: inserts cnf_permission rows (none that already exist); nothing else changes.
 */
return new class extends Migration
{
    private const ROUTES = ['requisitions.index', 'requisitions.create', 'requisitions.print'];

    public function up(): void
    {
        $roleId = DB::table('cnf_role')->where('role_name_en', 'Vehicle Driver')->whereNull('deleted_at')->value('id');
        if (! $roleId) {
            return;
        }

        $now = now();
        foreach (DB::table('cnf_menu')->whereIn('route', self::ROUTES)->whereNull('deleted_at')->pluck('id') as $menuId) {
            $exists = DB::table('cnf_permission')->where('role_id', $roleId)->where('menu_id', $menuId)->whereNull('deleted_at')->exists();
            if (! $exists) {
                DB::table('cnf_permission')->insert([
                    'role_id' => $roleId, 'menu_id' => $menuId,
                    'created_at' => $now, 'updated_at' => $now, 'created_by' => 1, 'updated_by' => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        $roleId = DB::table('cnf_role')->where('role_name_en', 'Vehicle Driver')->value('id');
        $menuIds = DB::table('cnf_menu')->whereIn('route', self::ROUTES)->pluck('id');
        DB::table('cnf_permission')->where('role_id', $roleId)->whereIn('menu_id', $menuIds)->delete();
    }
};
