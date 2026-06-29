<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\User;
use App\Repositories\RequisitionRepository;
use App\Repositories\RequisitionItemRepository;
use App\Services\ApprovalService;
use App\Services\StockService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class RequisitionService
{
    protected $requisitionRepository;
    protected $itemRepository;
    protected $approvalService;
    protected $stockService;
    protected $notificationService;

    /**
     * RequisitionService constructor.
     *
     * @param RequisitionRepository $requisitionRepository
     * @param RequisitionItemRepository $itemRepository
     * @param ApprovalService $approvalService
     * @param StockService $stockService
     * @param NotificationService $notificationService
     */
    public function __construct(
        RequisitionRepository $requisitionRepository,
        RequisitionItemRepository $itemRepository,
        ApprovalService $approvalService,
        StockService $stockService,
        NotificationService $notificationService
    ) {
        $this->requisitionRepository = $requisitionRepository;
        $this->itemRepository = $itemRepository;
        $this->approvalService = $approvalService;
        $this->stockService = $stockService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get all requisitions for the currently authenticated user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAllForUser(User $user, array $filters = [])
    {
        $query = $this->requisitionRepository->allQuery()
            ->where('requested_by_user_id', $user->id)
            ->with('items') // Eager load items if needed
            ->orderBy('created_at', 'desc');

        // Filter by Status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by Date Range
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('requisition_date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->get();
    }

    /**
     * Get a single requisition by its ID.
     *
     * @param int $id
     * @return \App\Models\Requisition
     */
    public function findById(int $id)
    {
        return $this->requisitionRepository->find($id)->load('items.product.category');
    }

    /**
     * Get all pending requisition tasks for a specific user (approver).
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingRequisitionsForUser(User $user)
    {
        $allPendingTasks = $this->approvalService->getPendingTasksForUser($user);

        // Filter to get ONLY Requisition tasks and load the original requestor
        return $allPendingTasks->filter(function ($task) {
            return $task->requistion_id !== null;
        })->load('requisition.requestedBy', 'requisition.items.product');
    }

    /**
     * Validate and create a new requisition.
     *
     * @param array $data
     * @param string $action ('save_draft' or 'submit')
     * @return \App\Models\Requisition
     * @throws \Exception
     */
    public function store(array $data, string $action = 'submit')
    {
        $validator = Validator::make($data, [
            'requisition_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            // 'details.*.status' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // CRITICAL RULE: Check stock availability BEFORE submitting
        // if ($action === 'submit') {
        //     foreach ($data['details'] as $item) {
        //         if ($item['status'] === 'Not Available') {
        //             // Find the product name for a clearer error message
        //             $productName = \App\Models\Product::find($item['product_id'])->name ?? 'Item';
        //             throw new InvalidArgumentException("Cannot submit: '$productName' is not available in stock. Please remove it or save as draft.");
        //         }
        //     }
        // }
        /**
         * According to the meeting held on 27-Nov-2025, the stock availability check
         * has been revised. We will not check the 'status' field from the UI directly and
         * also not from the backend.
         */

        return DB::transaction(function () use ($data, $action) {
            try {
                $status = ($action === 'save_draft') ? 'draft' : 'pending';

                // 1. Create the main Requisition record
                $requisition = $this->requisitionRepository->create([
                    'requisition_date' => $data['requisition_date'],
                    'remarks' => $data['remarks'], // Keeping this on the main record is good practice for quick access
                    'status' => $status,
                    'current_serial' => 1,
                    'requested_by_user_id' => Auth::id(),
                ]);

                // 2. Create the RequisitionItem records
                foreach ($data['details'] as $item) {
                    $this->itemRepository->create([
                        'requisition_id' => $requisition->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'original_quantity' => $item['quantity'],
                    ]);
                }

                // 3. ONLY submit to the workflow if the action is 'submit'
                if ($action === 'submit') {
                    // CHANGE IS HERE: Pass the remarks as the 3rd argument
                    $this->approvalService->submitForApproval(
                        $requisition,
                        'Requisition Approval',
                        $data['remarks'] ?? null
                    );
                }

                return $requisition;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to create requisition: ' . $e->getMessage());
            }
        });
    }

    /**
     * Validate and update an existing draft requisition.
     */
    public function update(array $data, int $id, string $action = 'submit')
    {
        // $this->validateRequisition($data, $action);

        $validator = Validator::make($data, [
            'requisition_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $requisition = $this->requisitionRepository->find($id);
        if ($requisition->status !== 'draft') {
            throw new InvalidArgumentException('This requisition cannot be edited as it is already in process.');
        }

        return DB::transaction(function () use ($data, $requisition, $action) {
            try {
                $status = ($action === 'save_draft') ? 'draft' : 'pending';

                $this->requisitionRepository->update([
                    'requisition_date' => $data['requisition_date'],
                    'remarks' => $data['remarks'],
                    'status' => $status,
                ], $requisition->id);

                // Delete old items and recreate
                $requisition->items()->delete();
                $this->saveItems($requisition->id, $data['details']);

                if ($action === 'submit') {
                    $this->approvalService->submitForApproval($requisition, 'Requisition Approval');
                }

                return $requisition;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to update requisition: ' . $e->getMessage());
            }
        });
    }

    // Shared validation logic
    private function validateRequisition(array $data, string $action)
    {
        $validator = Validator::make($data, [
            'requisition_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        if ($action === 'submit') {
            foreach ($data['details'] as $item) {
                $stockSummary = $this->stockService->getAggregatedStockList(['product_id' => $item['product_id']]);
                $available = $stockSummary->first()->total_quantity ?? 0;

                if ($available <= 0) { // Simple check: available vs 0. Or use $available < $item['quantity'] if you want strict checking
                    $productName = \App\Models\Product::find($item['product_id'])->name ?? 'Item';
                    throw new InvalidArgumentException("Cannot submit: '$productName' is out of stock.");
                }
            }
        }
    }

    private function saveItems($requisitionId, array $items)
    {
        foreach ($items as $item) {
            $this->itemRepository->create([
                'requisition_id' => $requisitionId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                // Save the snapshot of the original request
                'original_quantity' => $item['quantity'],
            ]);
        }
    }

    /**
     * Delete a draft requisition by its ID.
     *
     * @param int $id
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        $requisition = $this->requisitionRepository->find($id);
        if ($requisition->status !== 'draft') {
            throw new InvalidArgumentException('Only draft requisitions can be deleted.');
        }

        DB::beginTransaction();
        try {
            // Deleting the main requisition should cascade and delete items
            $result = $this->requisitionRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete requisition');
        }
        DB::commit();

        return $result;
    }

    /**
     * Approve a pending requisition.
     */
    public function approveRequisition(Requisition $requisition, ?string $comments): void
    {
        $pendingTask = $requisition->approvalDetails()
            ->where('status', 'pending')
            ->first();
        if (!$pendingTask) {
            throw new InvalidArgumentException('No pending approval task found for this requisition.');
        }

        // --- NEW BACKEND CHECK: Ensure Store Admin (Role 3) has assigned stock ---
        if (Auth::user()->role_id == 3) {
            // Lazy load items if they aren't loaded yet
            foreach ($requisition->items as $item) {
                if (empty($item->lot_detail_id)) {
                    throw new InvalidArgumentException('Validation failed: All items must be assigned a Brand/Spec and saved before approval.');
                }
            }
        }
        // -----------------------------------------------------------------------

        $this->approvalService->approve($pendingTask, $comments);
    }

    /**
     * Reject a pending requisition.
     */
    public function rejectRequisition(Requisition $requisition, string $comments): void
    {
        if (empty($comments)) {
            throw new InvalidArgumentException('Comments are required for rejection.');
        }

        $pendingTask = $requisition->approvalDetails()
            ->where('status', 'pending')
            ->first();
        if (!$pendingTask) {
            throw new InvalidArgumentException('No pending approval task found for this requisition.');
        }

        $this->approvalService->reject($pendingTask, $comments);
    }

    /**
     * Get the total number of processed requisitions.
     * (Required for DashboardService)
     */
    public function getProcessedCount(): int
    {
        // We check for 'processed' status, or 'approved' if you consider approved as final.
        // Based on our previous logic, the final status set by the Store Keeper is 'approved' (or 'processed' if you changed it).
        // Let's check for both to be safe or just 'approved' if that's the final state in your DB.
        // Assuming 'approved' is the final state for now based on ApprovalService code.

        return $this->requisitionRepository->countByStatus('processed');
        // If you explicitly used 'processed' in your database for the final step, change 'approved' to 'processed'.
    }


    /**
     * Split specific items into a new requisition and notify Store Keepers.
     * * @param Requisition $originalReq
     * @param array $itemIds
     * @return Requisition
     */
    public function splitAndNotify(Requisition $originalReq, array $itemIds)
    {
        return DB::transaction(function () use ($originalReq, $itemIds) {

            // 1. Check if we are selecting ALL items
            $totalItemCount = $originalReq->items()->count();
            $isFullSelection = count($itemIds) >= $totalItemCount;

            // Common: Get names for notification
            $selectedItems = \App\Models\RequisitionItem::whereIn('id', $itemIds)
                ->where('requisition_id', $originalReq->id)
                ->with('product')
                ->get();

            if ($selectedItems->isEmpty()) {
                throw new InvalidArgumentException("Selected items not found in the original requisition.");
            }

            $missingProductNames = $selectedItems->pluck('product.name')->unique()->toArray();
            $productList = implode(', ', $missingProductNames);
            $storeKeepers = User::where('role_id', 4)->get();
            $url = route('lots.create'); // Link to Stock Entry

            // --- CASE A: FULL SELECTION (Notify Only) ---
            if ($isFullSelection) {
                $text = "Stock Required! All items unavailable for Requisition #{$originalReq->id}: {$productList}";

                foreach ($storeKeepers as $keeper) {
                    $this->notificationService->createNotification($keeper->id, $text, $url, Auth::id());
                }

                return null; // Indicate no new requisition created
            }

            // --- CASE B: PARTIAL SPLIT (Create New Req) ---

            // 2. Create New Requisition
            $newReq = $this->requisitionRepository->create([
                'requisition_date' => $originalReq->requisition_date,
                'remarks' => "Split from Requisition #{$originalReq->id}. " . $originalReq->remarks,
                'status' => 'pending',
                'current_serial' => 1,
                'requested_by_user_id' => $originalReq->requested_by_user_id,
            ]);

            // 3. Move Items
            foreach ($selectedItems as $item) {
                $item->update(['requisition_id' => $newReq->id]);
            }

            // 4. Auto-Submit New Req
            $this->approvalService->submitForApproval(
                $newReq,
                'Requisition Approval',
                'Auto-generated split requisition for out-of-stock items.'
            );


            // 5. Notify
            $text = "Stock Required! Items moved to Split Requisition #{$newReq->id}: {$productList}";

            foreach ($storeKeepers as $keeper) {
                $this->notificationService->createNotification($keeper->id, $text, $url, Auth::id());
            }

            return $newReq;
        });
    }
}
