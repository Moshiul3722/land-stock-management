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
            'name' => 'Paper',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        ProductSubCategory::create([
            'name' => 'Tissue',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 3
        ProductSubCategory::create([
            'name' => 'Spray',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 4
        ProductSubCategory::create([
            'name' => 'Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 5
        ProductSubCategory::create([
            'name' => 'Pencil',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 6
        ProductSubCategory::create([
            'name' => 'Soap',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 7
        ProductSubCategory::create([
            'name' => 'Medical',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 8
        ProductSubCategory::create([
            'name' => 'Toiletries',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);


        // Id = 9
        ProductSubCategory::create([
            'name' => 'File & Folder',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 10
        ProductSubCategory::create([
            'name' => 'Office Stationery',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 11
        ProductSubCategory::create([
            'name' => 'Battery',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 12
        ProductSubCategory::create([
            'name' => 'Electronics',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 13
        ProductSubCategory::create([
            'name' => 'Textile',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 14
        ProductSubCategory::create([
            'name' => 'Plastic Items',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 15
        ProductSubCategory::create([
            'name' => 'Crockery',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 16
        ProductSubCategory::create([
            'name' => 'Miscellaneous',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        
        // Id = 17
        ProductSubCategory::create([
            'name' => 'Cleaning',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
