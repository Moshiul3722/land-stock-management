<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Category management now lives on one screen (categories + sub-categories, modal CRUD),
 * so the sidebar keeps a single entry, "Add Category".
 *
 * Only cnf_menu.is_menu changes, for three rows; routes and role permissions are kept.
 */
return new class extends Migration
{
    private const HIDDEN = ['categories.index', 'subcategories.create', 'subcategories.index'];

    public function up(): void
    {
        DB::table('cnf_menu')->whereIn('route', self::HIDDEN)->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->whereIn('route', self::HIDDEN)->update(['is_menu' => '1']);
    }
};
