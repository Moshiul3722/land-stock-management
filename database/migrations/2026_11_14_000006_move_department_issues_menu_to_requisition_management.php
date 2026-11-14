<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sidebar: "Issued goods list" moves from the "Stock Management" group to the
 * "Requisition Management" group (the group of the pending-requisitions list).
 *
 * Data only: one cnf_menu row gets another submodule_id. Permissions are per menu, so
 * who can open the page does not change.
 */
return new class extends Migration
{
    public function up(): void
    {
        $target = DB::table('cnf_menu')->where('route', 'requisitions.pending')->value('submodule_id');
        if ($target) {
            DB::table('cnf_menu')->where('route', 'stock.department_issues')->update(['submodule_id' => $target]);
        }
    }

    public function down(): void
    {
        $original = DB::table('cnf_menu')->where('route', 'stock.index')->value('submodule_id');
        if ($original) {
            DB::table('cnf_menu')->where('route', 'stock.department_issues')->update(['submodule_id' => $original]);
        }
    }
};
