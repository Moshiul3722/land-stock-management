<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides "Create Budget (by purchase type)" from the sidebar. New budgets are defined from
 * the button on the purchase-type budget list, so the separate menu entry is not needed.
 *
 * Only cnf_menu.is_menu changes for that one row; the route and its role permissions
 * are kept, so the create button and the old /ptbudgets/create link still work.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'ptbudgets.create')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'ptbudgets.create')->update(['is_menu' => '1']);
    }
};
