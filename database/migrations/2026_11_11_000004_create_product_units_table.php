<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product units ("Pieces", "Box", ...) become a managed list.
 *
 * - New table `product_units`. `products.product_unit` keeps holding the unit name as
 *   text, exactly as before, so nothing that reads it changes.
 * - The list is filled from the unit names already used by products (pure numbers,
 *   which are data-entry slips, are left out).
 * - Adds the "Product Unit" entry to the sidebar under Product Setup and grants it to
 *   the roles that can already open the product list.
 * - Product management is one screen now, so its separate "View List" entry is hidden.
 */
return new class extends Migration
{
    private const ROUTE = 'product-units.index';

    public function up(): void
    {
        if (! Schema::hasTable('product_units')) {
            Schema::create('product_units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
            });
        }

        $now = now();

        if (DB::table('product_units')->count() === 0) {
            $names = DB::table('products')
                ->whereNull('deleted_at')
                ->whereNotNull('product_unit')
                ->distinct()
                ->orderBy('product_unit')
                ->pluck('product_unit')
                ->map(fn ($name) => trim((string) $name))
                ->filter(fn ($name) => $name !== '' && ! is_numeric($name))
                ->unique(fn ($name) => mb_strtolower($name));

            foreach ($names as $name) {
                DB::table('product_units')->insert([
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'created_by' => 1,
                ]);
            }
        }

        $productList = DB::table('cnf_menu')->where('route', 'products.index')->first();
        if (! $productList || DB::table('cnf_menu')->where('route', self::ROUTE)->exists()) {
            return;
        }

        $moduleId = DB::table('cnf_submodule')->where('id', $productList->submodule_id)->value('module_id');

        $submoduleId = DB::table('cnf_submodule')->insertGetId([
            'submodule_name_en' => 'Product Unit',
            'submodule_name_bn' => 'প্রোডাক্ট ইউনিট/একক',
            'module_id' => $moduleId,
            'is_active' => 1,
            'is_null' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $menuId = DB::table('cnf_menu')->insertGetId([
            'submodule_id' => $submoduleId,
            'menu_name_en' => 'Product Unit',
            'menu_name_bn' => 'প্রোডাক্ট ইউনিট/একক',
            'controller' => 'ProductUnit',
            'action' => 'View',
            'route' => self::ROUTE,
            'is_menu' => '1',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $roleIds = DB::table('cnf_permission')
            ->where('menu_id', $productList->id)
            ->whereNull('deleted_at')
            ->pluck('role_id')
            ->unique();

        foreach ($roleIds as $roleId) {
            DB::table('cnf_permission')->insert([
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        DB::table('cnf_menu')->where('route', 'products.index')->update(['is_menu' => '0']);
    }

    public function down(): void
    {
        $menuIds = DB::table('cnf_menu')->where('route', self::ROUTE)->pluck('id');
        $submoduleIds = DB::table('cnf_menu')->whereIn('id', $menuIds)->pluck('submodule_id');

        DB::table('cnf_permission')->whereIn('menu_id', $menuIds)->delete();
        DB::table('cnf_menu')->whereIn('id', $menuIds)->delete();
        DB::table('cnf_submodule')->whereIn('id', $submoduleIds)->delete();
        DB::table('cnf_menu')->where('route', 'products.index')->update(['is_menu' => '1']);

        Schema::dropIfExists('product_units');
    }
};
