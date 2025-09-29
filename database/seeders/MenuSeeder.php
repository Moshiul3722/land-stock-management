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
    }
}
