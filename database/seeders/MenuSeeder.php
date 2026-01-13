<?php

namespace Database\Seeders;

use App\Models\CnfMenu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        CnfMenu::create([
            'submodule_id' => 1,
            'menu_name_en' => 'Role List',
            'menu_name_bn' => 'রোলগুলোর তালিকা',
            'controller' => 'CnfRole',
            'action' => 'View',
            'route' => 'cnfRoles.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        CnfMenu::create([
            'submodule_id' => 1,
            'menu_name_en' => 'Add New Role',
            'menu_name_bn' => 'নতুন রোল অ্যাড',
            'controller' => 'CnfRole',
            'action' => 'View',
            'route' => 'cnfRoles.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 3
        CnfMenu::create([
            'submodule_id' => 1,
            'menu_name_en' => 'Menu Permission',
            'menu_name_bn' => 'মেন্যু পারমিশন',
            'controller' => 'CnfPermission',
            'action' => 'View',
            'route' => 'cnfPermissions.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 4
        CnfMenu::create([
            'submodule_id' => 2,
            'menu_name_en' => 'User List',
            'menu_name_bn' => 'ব্যবহারকারীর তালিকা',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'users.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 5
        CnfMenu::create([
            'submodule_id' => 2,
            'menu_name_en' => 'Create User',
            'menu_name_bn' => 'নতুন ব্যবহারকারী যুক্ত করুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'users.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 6
        CnfMenu::create([
            'submodule_id' => 2,
            'menu_name_en' => 'Edit User',
            'menu_name_bn' => 'ব্যবহারকারীর তথ্য হালনাগাদ করুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'users.edit',
            'is_menu' => 0,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 7
        CnfMenu::create([
            'submodule_id' => 1,
            'menu_name_en' => 'Edit Role',
            'menu_name_bn' => 'রোলের তথ্য হালনাগাদ করুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'cnfRoles.edit',
            'is_menu' => 0,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 8
        CnfMenu::create([
            'submodule_id' => 3,
            'menu_name_en' => 'Add Category',
            'menu_name_bn' => 'নতুন ক্যাটাগরী যুক্ত করুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'categories.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 9
        CnfMenu::create([
            'submodule_id' => 3,
            'menu_name_en' => 'View List',
            'menu_name_bn' => 'তালিকা দেখুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'categories.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 10
        CnfMenu::create([
            'submodule_id' => 4,
            'menu_name_en' => 'Add Product',
            'menu_name_bn' => 'নতুন প্রোডাক্ট যুক্ত করুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'products.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 11
        CnfMenu::create([
            'submodule_id' => 4,
            'menu_name_en' => 'View List',
            'menu_name_bn' => 'তালিকা দেখুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'products.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 12
        CnfMenu::create([
            'submodule_id' => 5,
            'menu_name_en' => 'View Lot',
            'menu_name_bn' => 'তালিকা দেখুন',
            'controller' => 'Lot',
            'action' => 'View',
            'route' => 'lots.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 13
        CnfMenu::create([
            'submodule_id' => 5,
            'menu_name_en' => 'Create Lot',
            'menu_name_bn' => 'তৈরি করুন',
            'controller' => 'Lot',
            'action' => 'View',
            'route' => 'lots.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 14
        CnfMenu::create([
            'submodule_id' => 5,
            'menu_name_en' => 'Lot Requests',
            'menu_name_bn' => 'লটের অনুরোধসমূহ',
            'controller' => 'Lot',
            'action' => 'View',
            'route' => 'lots.entry.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 15
        CnfMenu::create([
            'submodule_id' => 6,
            'menu_name_en' => 'View Product Stock List',
            'menu_name_bn' => 'প্রোডাক্ট স্টক তালিকা',
            'controller' => 'Stock',
            'action' => 'View',
            'route' => 'stock.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 16
        CnfMenu::create([
            'submodule_id' => 7,
            'menu_name_en' => 'Create Requisition',
            'menu_name_bn' => 'রিকুইজিশন তৈরি করুন',
            'controller' => 'requisition',
            'action' => 'View',
            'route' => 'requisitions.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 17
        CnfMenu::create([
            'submodule_id' => 7,
            'menu_name_en' => 'View Requisition List',
            'menu_name_bn' => 'রিকুইজিশন তালিকা',
            'controller' => 'requisition',
            'action' => 'View',
            'route' => 'requisitions.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 18
        CnfMenu::create([
            'submodule_id' => 7,
            'menu_name_en' => 'Requisition Request List',
            'menu_name_bn' => 'রিকুইজিশন অনুরোধের তালিকা',
            'controller' => 'requisition',
            'action' => 'View',
            'route' => 'requisitions.pending',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 19
        CnfMenu::create([
            'submodule_id' => 8,
            'menu_name_en' => 'Product List',
            'menu_name_bn' => 'প্রোডাক্টের তালিকা',
            'controller' => 'requisition',
            'action' => 'View',
            'route' => 'issued_items.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 20
        CnfMenu::create([
            'submodule_id' => 9,
            'menu_name_en' => 'Stock Report',
            'menu_name_bn' => 'স্টক রিপোর্ট',
            'controller' => 'report',
            'action' => 'View',
            'route' => 'reports.stock.current',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 21
        CnfMenu::create([
            'submodule_id' => 9,
            'menu_name_en' => 'Consumption Report',
            'menu_name_bn' => 'রিকুইজিশন রিপোর্ট',
            'controller' => 'report',
            'action' => 'View',
            'route' => 'reports.consumption',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        
        // Id = 22
        CnfMenu::create([
            'submodule_id' => 3,
            'menu_name_en' => 'Add Sub-Category',
            'menu_name_bn' => 'নতুন সাব-ক্যাটাগরী যুক্ত করুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'subcategories.create',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 23
        CnfMenu::create([
            'submodule_id' => 3,
            'menu_name_en' => 'View Sub-Category List',
            'menu_name_bn' => 'সাব-ক্যাটাগরী তালিকা দেখুন',
            'controller' => 'User',
            'action' => 'View',
            'route' => 'subcategories.index',
            'is_menu' => 1,
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
