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

        // Id = 3
        CnfSubmodule::create([
            'submodule_name_en' => 'Category Management',
            'submodule_name_bn' => 'ক্যাটাগরী  ব্যবস্থাপনা',
            'module_id' => 2,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 4
        CnfSubmodule::create([
            'submodule_name_en' => 'Product Management',
            'submodule_name_bn' => 'প্রোডাক্ট  ব্যবস্থাপনা',
            'module_id' => 2,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 5
        CnfSubmodule::create([
            'submodule_name_en' => 'Lot Management',
            'submodule_name_bn' => 'লট  ব্যবস্থাপনা',
            'module_id' => 3,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 6
        CnfSubmodule::create([
            'submodule_name_en' => 'Stock Management',
            'submodule_name_bn' => 'স্টক  ব্যবস্থাপনা',
            'module_id' => 3,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 7
        CnfSubmodule::create([
            'submodule_name_en' => 'Requisition Management',
            'submodule_name_bn' => 'রিকুইজিশন  ব্যবস্থাপনা',
            'module_id' => 4,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 8
        CnfSubmodule::create([
            'submodule_name_en' => 'My Products',
            'submodule_name_bn' => 'আমার প্রোডাক্ট',
            'module_id' => 4,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 9
        CnfSubmodule::create([
            'submodule_name_en' => 'Reports',
            'submodule_name_bn' => 'প্রতিবেদন',
            'module_id' => 5,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 10
        CnfSubmodule::create([
            'submodule_name_en' => 'Budget Management',
            'submodule_name_bn' => 'বাজেট ব্যবস্থাপনা',
            'module_id' => 3,
            'is_active' => 1,
            'is_null' => 0,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
    
}
