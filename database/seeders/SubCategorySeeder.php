<?php

namespace Database\Seeders;

use App\Models\ProductSubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        ProductSubCategory::create([
            'name' => 'Office Stationery',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        ProductSubCategory::create([
            'name' => 'Cleaning & Hygiene',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 3
        ProductSubCategory::create([
            'name' => 'Office Accessories',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 4
        ProductSubCategory::create([
            'name' => 'Electrical & Electronics',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 5
        ProductSubCategory::create([
            'name' => 'Office Electronics',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 6
        ProductSubCategory::create([
            'name' => 'Pantry & Household',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 7
        ProductSubCategory::create([
            'name' => 'Cleaning Equipment',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 8
        ProductSubCategory::create([
            'name' => 'Pantry & Appliances',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);


        // Id = 9
        ProductSubCategory::create([
            'name' => 'Pantry & Utensils',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 10
        ProductSubCategory::create([
            'name' => 'Computer Hardware',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 11
        ProductSubCategory::create([
            'name' => 'Computer Accessories',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 12
        ProductSubCategory::create([
            'name' => 'Power & Electrical',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 13
        ProductSubCategory::create([
            'name' => 'Networking Equipment',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 14
        ProductSubCategory::create([
            'name' => 'Storage Devices',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 15
        ProductSubCategory::create([
            'name' => 'Printer & Consumables',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 16
        ProductSubCategory::create([
            'name' => 'Cables & Connectivity',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 17
        ProductSubCategory::create([
            'name' => 'Networking Accessories',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 17
        ProductSubCategory::create([
            'name' => 'Surveillance System',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 18
        ProductSubCategory::create([
            'name' => 'Networking Services',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 19
        ProductSubCategory::create([
            'name' => 'Maintenance Tools',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 20
        ProductSubCategory::create([
            'name' => 'Display & AV Equipment',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 21 
        ProductSubCategory::create([
            'name' => 'IT Services',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 22
        ProductSubCategory::create([
            'name' => 'Converters & Adapters',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 23
        ProductSubCategory::create([
            'name' => 'Software & Licenses',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 24
        ProductSubCategory::create([
            'name' => 'Printer & Spare Parts',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 25
        ProductSubCategory::create([
            'name' => 'Data Center Infrastructure',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
