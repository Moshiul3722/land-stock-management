<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Section/Department" entry to the sidebar (Configuration > User Management)
 * and grants it to every role that can already open the user list.
 *
 * Data only: inserts one cnf_menu row and one cnf_permission row per role.
 * No table structure changes; nothing existing is modified.
 */
return new class extends Migration
{
    private const ROUTE = 'departments.index';

    public function up(): void
    {
        if (DB::table('cnf_menu')->where('route', self::ROUTE)->exists()) {
            return;
        }

        $userList = DB::table('cnf_menu')->where('route', 'users.index')->first();
        if (! $userList) {
            return; // Menu tables not seeded yet; nothing to attach to.
        }

        $now = now();

        $menuId = DB::table('cnf_menu')->insertGetId([
            'submodule_id' => $userList->submodule_id,
            'menu_name_en' => 'Section/Department',
            'menu_name_bn' => 'সেকশন/ডিপার্টমেন্ট',
            'controller' => 'Department',
            'action' => 'View',
            'route' => self::ROUTE,
            'is_menu' => '1',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $roleIds = DB::table('cnf_permission')
            ->where('menu_id', $userList->id)
            ->whereNull('deleted_at')
            ->pluck('role_id')
            ->unique();

        foreach ($roleIds as $roleId) {
            DB::table('cnf_permission')->insert([
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }

    public function down(): void
    {
        $menuIds = DB::table('cnf_menu')->where('route', self::ROUTE)->pluck('id');

        DB::table('cnf_permission')->whereIn('menu_id', $menuIds)->delete();
        DB::table('cnf_menu')->whereIn('id', $menuIds)->delete();
    }
};
