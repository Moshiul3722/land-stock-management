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
            'module_name_en' => 'Product Setup',
            'module_name_bn' => 'প্রোডাক্ট সেটাপ',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 3
        CnfModule::create([
            'module_name_en' => 'Stock Management',
            'module_name_bn' => 'স্টক ম্যানেজমেন্ট',
            'is_active' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
