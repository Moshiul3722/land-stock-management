<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides "Create User" from the sidebar. New users are added from the button on the
 * user list, so the separate menu entry is no longer needed.
 *
 * Only cnf_menu.is_menu changes for that one row; the route and its role permissions
 * are kept, so the old /createusers link still works.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'users.create')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'users.create')->update(['is_menu' => '1']);
    }
};
