<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a sidebar menu be placed before or after its neighbours.
 *
 * Adds one nullable column, cnf_menu.sort_order. Menus without a value keep their
 * current position (the sidebar falls back to id * 10), so nothing existing moves.
 * The "Section/Department" menu is then placed just above "User List".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cnf_menu', 'sort_order')) {
            Schema::table('cnf_menu', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('is_menu');
            });
        }

        $userListId = DB::table('cnf_menu')->where('route', 'users.index')->value('id');
        if ($userListId) {
            DB::table('cnf_menu')
                ->where('route', 'departments.index')
                ->update(['sort_order' => $userListId * 10 - 5]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cnf_menu', 'sort_order')) {
            Schema::table('cnf_menu', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
