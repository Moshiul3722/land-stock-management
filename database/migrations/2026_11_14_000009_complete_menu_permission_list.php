<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "Menu Permission" page: the list covers every page of the project and says what
 * each entry is.
 *
 * 1. Four pages that had no entry get one (hidden from the sidebar, is_menu = 0), and
 *    every role that could already reach them from the page they belong to keeps them:
 *      stock.show          <- roles with stock.index
 *      stock.issues        <- roles with stock.index
 *      lots.print          <- roles with lots.index
 *      requisitions.print  <- roles with requisitions.index or requisitions.pending
 * 2. Five entries are renamed to say what they open. None of these names is shown in
 *    the sidebar (hidden entries, or groups the sidebar lists under the group's name).
 *
 * Data only: adds rows to cnf_menu / cnf_permission and changes name columns. Nothing
 * is deleted.
 */
return new class extends Migration
{
    /** route => [submodule id, English name, Bangla name, routes whose roles get it] */
    private const NEW_MENUS = [
        'stock.show' => [6, 'Stock Details', 'স্টকের বিস্তারিত', ['stock.index']],
        'stock.issues' => [6, 'Product Issue Details', 'প্রোডাক্ট বিতরণের বিবরণ', ['stock.index']],
        'lots.print' => [5, 'Print Lot', 'লট প্রিন্ট', ['lots.index']],
        'requisitions.print' => [7, 'Print Requisition Slip', 'রিকুইজিশন স্লিপ প্রিন্ট', ['requisitions.index', 'requisitions.pending']],
    ];

    /** route => [new English, new Bangla, old English, old Bangla] */
    private const RENAMES = [
        'categories.create' => ['Category Management', 'ক্যাটাগরী ব্যবস্থাপনা', 'Add Category', 'নতুন ক্যাটাগরী যুক্ত করুন'],
        'categories.index' => ['Category List', 'ক্যাটাগরীর তালিকা', 'View List', 'তালিকা দেখুন'],
        'products.create' => ['Product Management', 'প্রোডাক্ট ব্যবস্থাপনা', 'Product List', 'প্রোডাক্টের তালিকা'],
        'products.index' => ['Product List', 'প্রোডাক্টের তালিকা', 'View List', 'তালিকা দেখুন'],
        'lots.create' => ['Create Lot', 'নতুন লট তৈরি করুন', 'Create Lot', 'তৈরি করুন'],
        'issued_items.index' => ['My Received Products', 'আমার প্রাপ্ত প্রোডাক্টের তালিকা', 'Product List', 'প্রোডাক্টের তালিকা'],
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $now = now();

            foreach (self::NEW_MENUS as $route => [$submoduleId, $nameEn, $nameBn, $grantedWith]) {
                $menuId = DB::table('cnf_menu')->where('route', $route)->whereNull('deleted_at')->value('id');
                if (! $menuId) {
                    $menuId = DB::table('cnf_menu')->insertGetId([
                        'submodule_id' => $submoduleId,
                        'menu_name_en' => $nameEn,
                        'menu_name_bn' => $nameBn,
                        'route' => $route,
                        'is_menu' => 0,
                        'is_active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
                }

                $roleIds = DB::table('cnf_permission')
                    ->join('cnf_menu', 'cnf_menu.id', '=', 'cnf_permission.menu_id')
                    ->whereIn('cnf_menu.route', $grantedWith)
                    ->whereNull('cnf_permission.deleted_at')
                    ->whereNull('cnf_menu.deleted_at')
                    ->distinct()
                    ->pluck('cnf_permission.role_id');

                foreach ($roleIds as $roleId) {
                    $exists = DB::table('cnf_permission')
                        ->where('role_id', $roleId)->where('menu_id', $menuId)->whereNull('deleted_at')->exists();
                    if (! $exists) {
                        DB::table('cnf_permission')->insert([
                            'role_id' => $roleId,
                            'menu_id' => $menuId,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'created_by' => 1,
                            'updated_by' => 1,
                        ]);
                    }
                }
            }

            foreach (self::RENAMES as $route => [$nameEn, $nameBn]) {
                DB::table('cnf_menu')->where('route', $route)->update(['menu_name_en' => $nameEn, 'menu_name_bn' => $nameBn]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            foreach (self::RENAMES as $route => [, , $oldEn, $oldBn]) {
                DB::table('cnf_menu')->where('route', $route)->update(['menu_name_en' => $oldEn, 'menu_name_bn' => $oldBn]);
            }

            $menuIds = DB::table('cnf_menu')->whereIn('route', array_keys(self::NEW_MENUS))->pluck('id');
            DB::table('cnf_permission')->whereIn('menu_id', $menuIds)->delete();
            DB::table('cnf_menu')->whereIn('id', $menuIds)->delete();
        });
    }
};
