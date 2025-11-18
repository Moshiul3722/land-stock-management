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

class RequisitionService
{
    protected $requisitionRepository;
    protected $itemRepository;
    protected $approvalService;
    protected $stockService;

    /**
     * RequisitionService constructor.
     *
     * @param RequisitionRepository $requisitionRepository
     * @param RequisitionItemRepository $itemRepository
     * @param ApprovalService $approvalService
     * @param StockService $stockService
     */
    public function __construct(
        RequisitionRepository $requisitionRepository,
        RequisitionItemRepository $itemRepository,
        ApprovalService $approvalService,
        StockService $stockService
    ) {
        $this->requisitionRepository = $requisitionRepository;
        $this->itemRepository = $itemRepository;
        $this->approvalService = $approvalService;
        $this->stockService = $stockService;
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
            'details.*.status' => 'required|string', // Hidden field for stock status
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        // CRITICAL RULE: Check stock availability BEFORE submitting
        if ($action === 'submit') {
            foreach ($data['details'] as $item) {
                if ($item['status'] === 'Not Available') {
                    // Find the product name for a clearer error message
                    $productName = \App\Models\Product::find($item['product_id'])->name ?? 'Item';
                    throw new InvalidArgumentException("Cannot submit: '$productName' is not available in stock. Please remove it or save as draft.");
                }
            }
        }

        return DB::transaction(function () use ($data, $action) {
            try {
                $status = ($action === 'save_draft') ? 'draft' : 'pending';

                // 1. Create the main Requisition record
                $requisition = $this->requisitionRepository->create([
                    'requisition_date' => $data['requisition_date'],
                    'requested_by_user_id' => Auth::id(),
                    'remarks' => $data['remarks'],
                    'status' => $status,
                    'current_serial' => 1,
                ]);

                // 2. Create the RequisitionItem records
                foreach ($data['details'] as $item) {
                    $this->itemRepository->create([
                        'requisition_id' => $requisition->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }

                // 3. ONLY submit to the workflow if the action is 'submit'
                if ($action === 'submit') {
                    $this->approvalService->submitForApproval($requisition, 'Requisition Approval');
                }

                return $requisition;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to create requisition: ' . $e->getMessage());
            }
        });
    }

    /**
     * Placeholder for updating a draft requisition.
     *
     * @param array $data
     * @param int $id
     * @param string $action
     * @return \App\Models\Requisition
     */
    public function update(array $data, int $id, string $action = 'submit')
    {
        // This logic will be nearly identical to the LotService update method.
        // It should check if the status is 'draft', then update, delete/re-create items,
        // and finally call submitForApproval if $action is 'submit'.

        // Placeholder implementation
        $requisition = $this->requisitionRepository->find($id);
        if ($requisition->status !== 'draft') {
            throw new InvalidArgumentException('This requisition is already in process and cannot be edited.');
        }

        // Add full update logic here...

        return $requisition;
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
            ->firstOrFail();

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
            ->firstOrFail();

        $this->approvalService->reject($pendingTask, $comments);
    }
}
