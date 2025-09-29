<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CnfSubmodule;

class SubModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        CnfSubmodule::create([
            'submodule_name_en' => 'Role Management',
            'submodule_name_bn' => 'রোল  ব্যবস্থাপনা',
            'module_id' => 1,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        CnfSubmodule::create([
            'submodule_name_en' => 'User Management',
            'submodule_name_bn' => 'ব্যবহারকারী  ব্যবস্থাপনা',
            'module_id' => 1,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
