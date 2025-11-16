<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Black Ball Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Blue Ball Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'A4 Paper',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'A3 Paper',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Paperweight',
            'category_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
