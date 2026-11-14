<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides "Create requisition" from the sidebar.
 *
 * Only cnf_menu.is_menu changes for that one row; the route and its role permissions are
 * kept, so requisitions are still created from the requisition list ("new requisition" button).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'requisitions.create')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'requisitions.create')->update(['is_menu' => '1']);
    }
};
