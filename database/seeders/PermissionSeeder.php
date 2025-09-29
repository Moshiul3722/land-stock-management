<?php

namespace Database\Seeders;

use App\Models\CnfPermission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        CnfPermission::create(

            [
                'role_id' => 1,
                'menu_id' => 1,
                'created_by' => 1,
                'updated_by' => 1
            ]
        );

        // Id = 2
        CnfPermission::create(
            [
                'role_id' => 1,
                'menu_id' => 2,
                'created_by' => 1,
                'updated_by' => 1
            ],
        );

        // Id = 3
        CnfPermission::create(
            [
                'role_id' => 1,
                'menu_id' => 3,
                'created_by' => 1,
                'updated_by' => 1
            ],
        );

        // Id = 4
        CnfPermission::create(
            [
                'role_id' => 1,
                'menu_id' => 4,
                'created_by' => 1,
                'updated_by' => 1
            ],
        );

        // Id = 5
        CnfPermission::create(
            [
                'role_id' => 1,
                'menu_id' => 5,
                'created_by' => 1,
                'updated_by' => 1
            ],
        );

        // Id = 6
        CnfPermission::create(
            [
                'role_id' => 1,
                'menu_id' => 6,
                'created_by' => 1,
                'updated_by' => 1
            ],
        );

        // Id = 7
        CnfPermission::create(
            [
                'role_id' => 1,
                'menu_id' => 7,
                'created_by' => 1,
                'updated_by' => 1
            ],
        );
    }
}
