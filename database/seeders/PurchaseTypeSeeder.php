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
            'name' => 'RFQ',
            'description' => 'These items are RFQ based',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 2
        ProductPurchaseType::create([
            'name' => 'DPM',
            'description' => 'These items are DPM based',
            'created_by' => 1,
            'updated_by' => 1
        ]);
        // Id = 3
        ProductPurchaseType::create([
            'name' => 'OTM',
            'description' => 'These items are OTM based',
            'created_by' => 1,
            'updated_by' => 1
        ]);

        // Id = 4
        ProductPurchaseType::create([
            'name' => 'LTM',
            'description' => 'These items are LTM based',
            'created_by' => 1,
            'updated_by' => 1
        ]);
    }
}
