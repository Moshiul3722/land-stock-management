<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sidebar: the entry under "Product Management" is called "Product list" instead of
 * "Add new product" (it opens the product list, where products are added in a dialog).
 *
 * Only the two name columns of that one cnf_menu row change; route and permissions are kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cnf_menu')->where('route', 'products.create')->update([
            'menu_name_bn' => 'প্রোডাক্টের তালিকা',
            'menu_name_en' => 'Product List',
        ]);
    }

    public function down(): void
    {
        DB::table('cnf_menu')->where('route', 'products.create')->update([
            'menu_name_bn' => 'নতুন প্রোডাক্ট যুক্ত করুন',
            'menu_name_en' => 'Add Product',
        ]);
    }
};
