<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Advisers Office
        // Id = 1
        Department::create([
            'name' => 'Advisers Office',
            'name_bn' => 'উপদেষ্টার দপ্তর',
        ]);
        // Office of The Senior Secretary
        // Id = 2
        Department::create([
            'name' => 'Office of The Senior Secretary',
            'name_bn' => 'সিনিয়র সচিবের দপ্তর',
        ]);
        // Admin
        // Id = 3
        Department::create([
            'name' => 'Admin',
            'name_bn' => 'প্রশাসন',
        ]);
        // Development
        // Id = 4
        Department::create([
            'name' => 'Development',
            'name_bn' => 'উন্নয়ন',
        ]);
        // Law
        // Id = 5
        Department::create([
            'name' => 'Law',
            'name_bn' => 'আইন',
        ]);
        // Field Admin
        // Id = 6
        Department::create([
            'name' => 'Field Admin',
            'name_bn' => 'মাঠ প্রশাসন',
        ]);
        // Acquisition & Khash Land
        // Id = 7
        Department::create([
            'name' => 'Acquisition & Khash Land',
            'name_bn' => 'অধিগ্রহণ ও খাসজমি',
        ]);
        // Survey & Sairat
        // Id = 8
        Department::create([
            'name' => 'Survey & Sairat',
            'name_bn' => 'জরিপ ও সায়রাত',
        ]);
        // DKMP
        // Id = 9
        Department::create([
            'name' => 'DKMP',
            'name_bn' => 'ডিকেএমপি',
        ]);
    }
}
