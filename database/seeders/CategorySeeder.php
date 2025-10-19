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
            'name' => 'Stationary',
            'description' => 'Pens, Pencils, Notebooks, and other stationary items',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        ProductCategory::create([
            'name' => 'Desk Organizers',
            'description' => 'Desk organizers and related items',
            'created_by' => 1,
            'updated_by' => 1
        ]);


        // Id = 3
        ProductCategory::create([
            'name' => 'Electronics',
            'description' => 'Facilities related to electronic items',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
