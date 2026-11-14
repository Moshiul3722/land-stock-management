<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Gives Super Admin (role 1) and Admin (role 2) every entry of cnf_menu.
 *
 * Data only: inserts the cnf_permission rows that are missing for those two roles.
 * No table structure changes; no existing row is modified or removed.
 */
return new class extends Migration
{
    private const ROLE_IDS = [1, 2];

    public function up(): void
    {
        $menuIds = DB::table('cnf_menu')->whereNull('deleted_at')->pluck('id');
        $now = now();

        foreach (self::ROLE_IDS as $roleId) {
            if (! DB::table('cnf_role')->where('id', $roleId)->exists()) {
                continue;
            }

            $granted = DB::table('cnf_permission')
                ->where('role_id', $roleId)
                ->whereNull('deleted_at')
                ->pluck('menu_id');

            foreach ($menuIds->diff($granted) as $menuId) {
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
    }

    public function down(): void
    {
        // Nothing to undo automatically: the rows cannot be told apart from permissions that
        // were granted by hand. Use the permission screen to take a permission away again.
    }
};
