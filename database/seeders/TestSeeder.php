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
            'name_bn' => 'কালো বল পেন',
            'category_id' => 1,
            'purchase_type_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Blue Ball Pen',
            'name_bn' => 'নীল বল পেন',
            'category_id' => 1,
            'purchase_type_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'A4 Paper',
            'name_bn' => 'এ৪ কাগজ',
            'category_id' => 1,
            'purchase_type_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'A3 Paper',
            'name_bn' => 'এ৩ কাগজ',
            'category_id' => 1,
            'purchase_type_id' => 1,
            'created_by' => 1,
            'updated_by' => 1
        ]);
        Product::create([
            'name' => 'Paperweight',
            'name_bn' => 'পেপারওয়েট',
            'category_id' => 2,
            'purchase_type_id' => 2,
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
