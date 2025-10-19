<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalProcess;

class ApprovalProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // === LOT APPROVAL WORKFLOW ===
        // This workflow has ONE step:
        // 1. Store Admin (Role ID: 3) approves the lot.
        ApprovalProcess::updateOrCreate(
            ['approval_entity_id' => 1, 'serial' => 1],
            ['role_id' => 3, 'is_approver' => true, 'is_processor' => false] // Role ID 3 = Store Admin
        );


        // === REQUISITION APPROVAL WORKFLOW ===
        // This workflow has THREE steps:
        // 1. Store Admin (Role ID: 3) gives the first approval.
        ApprovalProcess::updateOrCreate(
            ['approval_entity_id' => 2, 'serial' => 1],
            ['role_id' => 3, 'is_approver' => true, 'is_processor' => false] // Role ID 3 = Store Admin
        );

        // 2. Admin (Role ID: 2) gives the final approval.
        ApprovalProcess::updateOrCreate(
            ['approval_entity_id' => 2, 'serial' => 2],
            ['role_id' => 2, 'is_approver' => true, 'is_processor' => false] // Role ID 2 = Admin
        );
        
        // 3. Store Keeper (Role ID: 4) processes the approved requisition.
        ApprovalProcess::updateOrCreate(
            ['approval_entity_id' => 2, 'serial' => 3],
            ['role_id' => 4, 'is_approver' => false, 'is_processor' => true] // Role ID 4 = Store Keeper
        );
    }
}
