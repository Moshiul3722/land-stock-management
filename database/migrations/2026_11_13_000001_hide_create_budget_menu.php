<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides "Create Budget" (বাজেট তৈরি করুন) from the sidebar. New budgets are defined from
 * the button on the budget list, so the separate menu entry is no longer needed.
 *
 * Only cnf_menu.is_menu changes for that one row; the route and its role permissions
 * are kept, so the create button and the old /budgets/create link still work.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'budgets.create')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'budgets.create')->update(['is_menu' => '1']);
    }
};
