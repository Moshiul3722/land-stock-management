<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides "Product list" (the signed-in user's received products) from the sidebar's
 * "My Products" group.
 *
 * Only cnf_menu.is_menu changes for that one row; the route and its role permissions are
 * kept, so the page itself still opens for anyone who has its address.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'issued_items.index')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'issued_items.index')->update(['is_menu' => '1']);
    }
};
