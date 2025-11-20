<?php

namespace Database\Seeders;

use App\Models\CnfRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        // Id = 1
        CnfRole::create([
            'role_name_en' => 'Super Admin',
            'role_name_bn' => 'সুপার অ্যাডমিন',
            'created_by' => '1',
        ]);
        // Admin (DS)
        // Id = 2
        CnfRole::create([
            'role_name_en' => 'Admin',
            'role_name_bn' => 'অ্যাডমিন',
            'created_by' => '1',
        ]);
        // Store Admin (Administrative Officer)
        // Id = 3
        CnfRole::create([
            'role_name_en' => 'Store Admin',
            'role_name_bn' => 'স্টোর অ্যাডমিন',
            'created_by' => '1',
        ]);
        // Store Keeper
        // Id = 4
        CnfRole::create([
            'role_name_en' => 'Store Keeper',
            'role_name_bn' => 'স্টোর কিপার',
            'created_by' => '1',
        ]);
        // Inventory User
        // Id = 5
        CnfRole::create([
            'role_name_en' => 'User',
            'role_name_bn' => 'ব্যবহারকারী',
            'created_by' => '1',
        ]);

        // Id = 1
        User::create([
            'name' => 'Super Admin-1',
            'name_bn' => 'সুপার অ্যাডমিন-১',
            'email' => 'superadmin1@land.com',
            'role_id' => '1',
            'created_by' => '1',
            'updated_by' => '1',
            'password' => Hash::make('admin'),
        ]);

        // Id = 2
        User::create([
            'name' => 'Super Admin-2',
            'name_bn' => 'সুপার অ্যাডমিন-২',
            'email' => 'superadmin2@land.com',
            'role_id' => '1',
            'created_by' => '1',
            'updated_by' => '1',
            'password' => Hash::make('admin'),
        ]);

        // Admin User
        User::updateOrCreate(['email' => 'admin1@land.com'], [
            'name' => 'Admin-1',
            'name_bn' => 'অ্যাডমিন-১',
            'role_id' => 2,
            'password' => Hash::make('admin'),
            'created_by' => 1
        ]);

        // Admin User
        User::updateOrCreate(['email' => 'admin2@land.com'], [
            'name' => 'Admin-2',
            'name_bn' => 'অ্যাডমিন-২',
            'role_id' => 2,
            'password' => Hash::make('admin'),
            'created_by' => 1
        ]);

        // Store Admin User - 1
        User::updateOrCreate(['email' => 'storeadmin1@land.com'], [
            'name' => 'Store Admin-1',
            'name_bn' => 'স্টোর অ্যাডমিন-১',
            'role_id' => 3,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);

        // Store Admin User - 2
        User::updateOrCreate(['email' => 'storeadmin2@land.com'], [
            'name' => 'Store Admin-2',
            'name_bn' => 'স্টোর অ্যাডমিন-২',
            'role_id' => 3,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);

        // Store Keeper User
        User::updateOrCreate(['email' => 'storekeeper1@land.com'], [
            'name' => 'Store Keeper-1',
            'name_bn' => 'স্টোর কিপার-১',
            'role_id' => 4,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);

        // Store Keeper User
        User::updateOrCreate(['email' => 'storekeeper2@land.com'], [
            'name' => 'Store Keeper-2',
            'name_bn' => 'স্টোর কিপার-২',
            'role_id' => 4,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);

        // Inventory User
        User::updateOrCreate(['email' => 'user1@land.com'], [
            'name' => 'User-1',
            'name_bn' => 'ব্যবহারকারী-১',
            'role_id' => 5,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);
        // Inventory User
        User::updateOrCreate(['email' => 'user2@land.com'], [
            'name' => 'User-2',
            'name_bn' => 'ব্যবহারকারী-২',
            'role_id' => 5,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);
        // Inventory User
        User::updateOrCreate(['email' => 'user3@land.com'], [
            'name' => 'User-3',
            'name_bn' => 'ব্যবহারকারী-৩',
            'role_id' => 5,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);
        // Inventory User
        User::updateOrCreate(['email' => 'user4@land.com'], [
            'name' => 'User-4',
            'name_bn' => 'ব্যবহারকারী-৪',
            'role_id' => 5,
            'password' => Hash::make('password'),
            'created_by' => 1
        ]);
    }
}
