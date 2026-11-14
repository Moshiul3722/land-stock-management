<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sidebar: "Issued goods list" (প্রদানকৃত মালামালের তালিকা) is renamed to
 * "Distributed goods list" (বিতরণকৃত মালামালের তালিকা).
 *
 * Data only: the two name columns of one cnf_menu row.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'stock.department_issues')->update([
            'menu_name_en' => 'Distributed Goods List',
            'menu_name_bn' => 'বিতরণকৃত মালামালের তালিকা',
        ]);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'stock.department_issues')->update([
            'menu_name_en' => 'Issued Goods List',
            'menu_name_bn' => 'প্রদানকৃত মালামালের তালিকা',
        ]);
    }
};
