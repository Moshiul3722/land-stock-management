<?php

namespace App\Services;

use App\Models\Lot;
use App\Repositories\LotRepository;
use App\Repositories\LotDetailRepository;
use App\Services\ApprovalService;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class LotService
{
    protected $lotRepository;
    protected $lotDetailRepository;
    protected $approvalService;

    public function __construct(
        LotRepository $lotRepository,
        LotDetailRepository $lotDetailRepository,
        ApprovalService $approvalService
    ) {
        $this->lotRepository = $lotRepository;
        $this->lotDetailRepository = $lotDetailRepository;
        $this->approvalService = $approvalService;
    }

    /**
     * Get lots, optionally filtered by status, ordered by latest first.
     *
     * @param string|null $statusFilter
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchAll(?string $statusFilter = null)
    {
        $query = $this->lotRepository->allQuery()
            ->with(['lotDetails.product', 'purchaseType'])
            ->orderBy('created_at', 'desc');
        // Apply the status filter if provided
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        return $query->get();
    }

    /**
     * Get a single lot by its ID.
     *
     * @param int $id
     * @return \App\Models\Lot
     */
    public function findById($id)
    {
        return $this->lotRepository->find($id)->load(['lotDetails.product', 'purchaseType']);
    }

    /**
     * Validate, create a new lot with its details, and submit for approval.
     *
     * @param array $data
     * @return \App\Models\Lot
     * @throws \Exception
     */
    public function store(array $data, string $action = 'submit')
    {
        $validator = Validator::make($data, [
            'lot_number' => 'required|string|max:255|unique:lots',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'product_purchase_type_id' => 'required|exists:product_purchase_types,id',
            'entry_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.brand' => 'nullable|string',
            'details.*.specification' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return DB::transaction(function () use ($data, $action) {
            try {
                $status = ($action === 'save_draft') ? 'draft' : 'pending';

                // 1. Create the main Lot record with the correct status
                $lot = $this->lotRepository->create([
                    'lot_number' => $data['lot_number'],
                    'fiscal_year_id' => $data['fiscal_year_id'],
                    'product_purchase_type_id' => $data['product_purchase_type_id'],
                    'entry_date' => $data['entry_date'],
                    'remarks' => $data['remarks'],
                    'status' => $status,
                ]);

                // 2. Create the LotDetail records
                if (!empty($data['details'])) {
                    foreach ($data['details'] as $item) {
                        $item['lot_id'] = $lot->id;
                        $this->lotDetailRepository->create($item);
                    }
                }

                // 3. ONLY submit to the workflow if the action is 'submit'
                if ($action === 'submit') {
                    $this->approvalService->submitForApproval($lot, 'Lot Approval',  $data['remarks'] ?? null);
                }

                return $lot;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to process lot: ' . $e->getMessage());
            }
        });
    }

    /**
     * Validate and update an existing lot and its details.
     *
     * @param array $data
     * @param int $id
     * @param string $action ('save_draft' or 'submit')
     * @return \App\Models\Lot
     * @throws \Exception
     */
    public function update(array $data, int $id, string $action = 'submit')
    {
        $validator = Validator::make($data, [
            'lot_number' => 'required|string|max:255|unique:lots,lot_number,' . $id,
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
            'product_purchase_type_id' => 'required|exists:product_purchase_types,id',
            'entry_date' => 'required|date',
            'remarks' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
            'details.*.unit_price' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        $lot = $this->lotRepository->find($id);
        if ($lot->status !== 'draft') {
            throw new InvalidArgumentException('This lot cannot be edited as it is already in the approval process.');
        }

        return DB::transaction(function () use ($data, $lot, $action) {
            try {
                $status = ($action === 'save_draft') ? 'draft' : 'pending';

                // 1. Update the main Lot record
                $updatedLot = $this->lotRepository->update([
                    'lot_number' => $data['lot_number'],
                    'fiscal_year_id' => $data['fiscal_year_id'],
                    'product_purchase_type_id' => $data['product_purchase_type_id'],
                    'entry_date' => $data['entry_date'],
                    'remarks' => $data['remarks'],
                    'status' => $status,
                ], $lot->id);

                // 2. Delete old details to replace them
                $updatedLot->lotDetails()->delete();

                // 3. Create the new LotDetail records
                foreach ($data['details'] as $item) {
                    $item['lot_id'] = $updatedLot->id;
                    $this->lotDetailRepository->create($item);
                }

                // 4. ONLY submit to the workflow if the action is 'submit'
                if ($action === 'submit') {
                    $this->approvalService->submitForApproval($updatedLot, 'Lot Approval',  $data['remarks'] ?? null);
                }

                return $updatedLot;
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new InvalidArgumentException('Unable to update lot: ' . $e->getMessage());
            }
        });
    }

    /**
     * Delete a lot by its ID.
     *
     * @param int $id
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $result = $this->lotRepository->delete($id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            throw new InvalidArgumentException('Unable to delete lot');
        }
        DB::commit();

        return $result;
    }

    /**
     * Get all pending lot tasks for a specific user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingLotsForUser(User $user)
    {
        // 1. Get all pending tasks from the ApprovalService
        $allPendingTasks = $this->approvalService->getPendingTasksForUser($user);
        // 2. Filter the collection to get ONLY Lot requests
        $pendingLotTasks = $allPendingTasks->filter(function ($task) {
            return $task->lot_id !== null;
        });

        return $pendingLotTasks;
    }

    /**
     * Get all lots that are currently pending approval.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPendingLots()
    {
        // This is now a simple, direct query on the Lots table
        // We eager load the relationships needed for the view
        return $this->lotRepository->allQuery(
            ['status' => 'pending'], // Filter by 'pending' status
            null,
            null
        )->with(['purchaseType', 'approvalDetails.fromUser'])
            ->orderBy('created_at', 'asc') // Show oldest first
            ->get();
    }

    /**
     * Approves a pending lot.
     *
     * @param Lot $lot
     * @param string|null $comments
     */
    public function approveLot(Lot $lot, ?string $comments): void
    {
        // This business logic now lives in the service
        $pendingTask = $lot->approvalDetails()
            ->where('status', 'pending')
            ->firstOrFail(); // Fails if no pending task

        // Call the approval service with the specific task
        $this->approvalService->approve($pendingTask, $comments);
    }

    /**
     * Rejects a pending lot.
     *
     * @param Lot $lot
     * @param string $comments
     */
    public function rejectLot(Lot $lot, string $comments): void
    {
        // This validation logic now lives in the service
        if (empty($comments)) {
            throw new InvalidArgumentException('Comments are required for rejection.');
        }

        $pendingTask = $lot->approvalDetails()
            ->where('status', 'pending')
            ->firstOrFail();

        $this->approvalService->reject($pendingTask, $comments);
    }

    /**
     * Search for existing brands or specifications for autocomplete.
     *
     * @param string|null $term
     * @param string|null $type
     * @return \Illuminate\Support\Collection|array
     */
    public function getAutocompleteSuggestions(?string $term, ?string $type)
    {
        // Basic validation of the type to prevent SQL injection or errors
        if (!in_array($type, ['brand', 'specification'])) {
            return [];
        }

        // Search in lot_details to find previously used brands/specs
        // Using DB facade here is efficient for simple distinct queries
        return DB::table('lot_details')
            ->where($type, 'LIKE', '%' . $term . '%')
            ->whereNotNull($type)
            ->distinct()
            ->orderBy($type)
            ->limit(10)
            ->pluck($type);
    }
}
