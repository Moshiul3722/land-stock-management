<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CnfModule;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        CnfModule::create([
            'module_name_en' => 'Configuration',
            'module_name_bn' => 'কনফিগারেশন',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 2
        CnfModule::create([
            'module_name_en' => 'Data Entry',
            'module_name_bn' => 'তথ্য পূরণ',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 3
        CnfModule::create([
            'module_name_en' => 'All Reports',
            'module_name_bn' => 'সমস্ত রিপোর্ট',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 4
        CnfModule::create([
            'module_name_en' => 'Approval',
            'module_name_bn' => 'অ্যাপ্রুভাল',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
