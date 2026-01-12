<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use App\Models\ProductPurchaseType;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        ProductCategory::create([
            'name' => 'মনিহারি সামগ্রী',
            'description' => 'Pens, Pencils, Notebooks, and other stationary items',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        ProductCategory::create([
            'name' => 'কম্পিউটার সামগ্রী',
            'description' => 'Computer related facilities and accessories',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 3
        ProductCategory::create([
            'name' => 'আইসিটি সামগ্রী',
            'description' => 'ICT related facilities and accessories',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
