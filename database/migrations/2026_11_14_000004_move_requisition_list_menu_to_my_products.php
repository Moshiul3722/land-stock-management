<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sidebar: "Requisition list" moves from the "Requisition Management" group to the
 * "My Products" group (the group that holds the issued-items list).
 *
 * Data only: one cnf_menu row gets another submodule_id. Permissions are per menu, so
 * who can open the page does not change.
 */
return new class extends Migration
{
    public function up(): void
    {
        $target = DB::table('cnf_menu')->where('route', 'issued_items.index')->value('submodule_id');
        if ($target) {
            DB::table('cnf_menu')->where('route', 'requisitions.index')->update(['submodule_id' => $target]);
        }
    }

    public function down(): void
    {
        $original = DB::table('cnf_menu')->where('route', 'requisitions.pending')->value('submodule_id');
        if ($original) {
            DB::table('cnf_menu')->where('route', 'requisitions.index')->update(['submodule_id' => $original]);
        }
    }
};
