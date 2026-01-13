<?php

namespace Database\Seeders;

use App\Models\ProductSubCategory;
use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get the path to the JSON file (located in the same directory)
        $jsonPath = __DIR__ . '/products.json';

        // 2. Read the file contents
        // You can use native PHP:
        $json = file_get_contents($jsonPath);

        // Or Laravel's File helper (if you imported it):
        // $json = File::get($jsonPath);

        // 3. Decode the JSON
        $products = json_decode($json, true);

        // 4. Loop and Create
        foreach ($products as $product) {
            Product::create([
                'category_id'     => $product['category_id'],
                'sub_category_id' => $product['sub_category_id'],
                'name'            => $product['name'],
                'product_unit'    => $product['product_unit'],
                'created_by'      => 1,
                'updated_by'      => 1
            ]);
        }

        // 1. Get the path to the JSON file (located in the same directory)
        $jsonPath = __DIR__ . '/products2.json';

        // 2. Read the file contents
        // You can use native PHP:
        $json = file_get_contents($jsonPath);

        // Or Laravel's File helper (if you imported it):
        // $json = File::get($jsonPath);

        // 3. Decode the JSON
        $products = json_decode($json, true);

        // 4. Loop and Create
        foreach ($products as $product) {
            Product::create([
                'category_id'     => $product['category_id'],
                'sub_category_id' => $product['sub_category_id'],
                'name'            => $product['name'],
                'product_unit'    => $product['product_unit'],
                'created_by'      => 1,
                'updated_by'      => 1
            ]);
        }
    }
}
