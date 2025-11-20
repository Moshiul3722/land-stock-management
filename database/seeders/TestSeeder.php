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
            'name' => 'Black Gel Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Blue Gel Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Black Marker Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Blue Marker Pen',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => '2B Pencil',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'HB Pencil',
            'category_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => '6B Pencil',
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
