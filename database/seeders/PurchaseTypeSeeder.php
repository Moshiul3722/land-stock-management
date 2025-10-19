<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductPurchaseType;

class PurchaseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Id = 1
        ProductPurchaseType::create([
            'name' => 'Tender-based',
            'description' => 'These items are tender-based',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        ProductPurchaseType::create([
            'name' => 'Direct',
            'description' => 'These items are directly purchased',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
