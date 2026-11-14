<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides "Budget list (by purchase type)" from the sidebar.
 *
 * Only cnf_menu.is_menu changes for that one row; the route and its role permissions
 * are kept, so the screen is still reachable at /ptbudgets and lot approval keeps
 * checking the purchase-type budgets as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'ptbudgets.index')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'ptbudgets.index')->update(['is_menu' => '1']);
    }
};
