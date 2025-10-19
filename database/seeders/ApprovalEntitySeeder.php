<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalEntity;

class ApprovalEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApprovalEntity::updateOrCreate(
            ['name' => 'Lot Approval'],
            ['description' => 'Workflow for approving new stock lots.']
        );

        ApprovalEntity::updateOrCreate(
            ['name' => 'Requisition Approval'],
            ['description' => 'Workflow for approving item requisitions.']
        );
    }
}