<?php

namespace App\Services;

use App\Models\ApprovalDetail;
use App\Models\ApprovalEntity;
use App\Models\Lot;
use App\Models\Requisition;
use App\Models\User;
use App\Models\Stock;
use App\Models\IssuedItem;
use App\Repositories\StockRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\Services\NotificationService;

class ApprovalService
{
    protected $stockRepository;
    protected $notificationService;

    public function __construct(StockRepository $stockRepository, NotificationService $notificationService)
    {
        $this->stockRepository = $stockRepository;
        $this->notificationService = $notificationService;
    }
    /**
     * Kicks off the approval workflow for a given model instance.
     *
     * @param Model $model The model instance (Lot or Requisition)
     * @param string $entityName The name of the process (e.g., "Lot Approval")
     * @param string|null $comments Optional comments from the submitter.
     */
    public function submitForApproval(Model $model, string $entityName, ?string $comments = null): void
    {
        DB::transaction(function () use ($model, $entityName, $comments) {
            // Start the workflow from the beginning (before serial 1)
            $this->advanceWorkflow($model, $entityName, 0, $comments);
        });
    }

    /**
     * Approves a specific step in the workflow.
     *
     * @param ApprovalDetail $pendingTask The pending task to be approved.
     * @param string|null $comments Optional comments from the approver.
     */
    public function approve(ApprovalDetail $pendingTask, string $comments = null): void
    {
        $this->processTask($pendingTask, 'approved', $comments);
    }

    /**
     * Rejects a specific step in the workflow.
     *
     * @param ApprovalDetail $pendingTask The pending task to be rejected.
     * @param string|null $comments Optional comments explaining the rejection.
     */
    public function reject(ApprovalDetail $pendingTask, string $comments = null): void
    {
        $this->processTask($pendingTask, 'rejected', $comments);
    }

    /**
     * Core logic for processing a task (approve/reject).
     */
    private function processTask(ApprovalDetail $pendingTask, string $status, ?string $comments): void
    {
        if ($pendingTask->status !== 'pending') {
            throw new InvalidArgumentException('This task is no longer pending.');
        }

        DB::transaction(function () use ($pendingTask, $status, $comments) {
            $model = $pendingTask->lot ?? $pendingTask->requisition;
            if (!$model) {
                throw new Exception('Associated model not found for the approval task.');
            }
            $entity = $pendingTask->approvalEntity;

            // 1. Log the user's action (approved/rejected) with their comments
            $this->createEvent($model, $entity->id, $pendingTask->serial, $status, Auth::id(), null, $comments);

            // 2. Mark other pending tasks at this level as superceded
            $this->supercedeOtherTasks($model, $entity->id, $pendingTask->serial);

            // 3. If approved, try to advance to the next step. Otherwise, stop the workflow.
            if ($status === 'approved') {
                $this->advanceWorkflow($model, $entity->name, $pendingTask->serial);
            } else {
                $model->update(['status' => 'rejected']);

                // 4. NOTIFY REQUESTOR ON REJECTION
                $this->notifyRequestorOfRejection($model);
            }
        });
    }

    /**
     * Finds and advances the workflow to the next step based on the rules.
     * Also accepts optional comments $initComments for the initial submission.
     */
    private function advanceWorkflow(Model $model, string $entityName, int $currentSerial, ?string $initComments = null): void
    {
        $entity = ApprovalEntity::where('name', $entityName)->firstOrFail();
        $nextStep = $entity->processes()->where('serial', $currentSerial + 1)->first();
        if ($nextStep) {
            // --- Move to Next Approver ---
            $nextApprovers = User::where('role_id', $nextStep->role_id)->get();
            if ($nextApprovers->isEmpty()) {
                throw new Exception("No users found for the role required in the next approval step.");
            }

            // === NEW LOGIC: Enable Print when moving to Store Keeper (Role 4) ===
            // This happens when the Admin approves and the next step is the Store Keeper.
            if ($nextStep->role_id == 4 && $model instanceof Requisition) {
                // 1. Enable Print Flag
                $model->update(['is_print_enabled' => true]);

                // 2. Notify Requestor that slip is ready
                $this->notifyRequestorReadyForPrint($model);
            }

            foreach ($nextApprovers as $approver) {
                $commentsToSave = ($currentSerial === 0) ? $initComments : null;
                $this->createEvent($model, $entity->id, $nextStep->serial, 'pending', Auth::id(), $approver->id, $commentsToSave);
            }
            $model->update(['current_serial' => $nextStep->serial]);
        } else {
            if ($model instanceof Lot) {
                // 1. NEW: Check Budget Availability & Deduct
                // This will throw an exception if budget is insufficient, rolling back the transaction
                // $this->processBudgetForLot($model);
                // 1. DEDUCT CATEGORY BUDGET (Existing)
                $this->processBudgetForLot($model);
                // 2. DEDUCT PURCHASE TYPE BUDGET (New)
                $this->processPurchaseTypeBudgetForLot($model);
                // 2. Stock In (If budget check passed)
                $model->update(['status' => 'approved']);
                $this->addLotItemsToStock($model);
                $this->notifyRequestorOfApproval($model);
            } elseif ($model instanceof Requisition) {
                // STOCK OUT: Mark as Delivered (Waiting for Acknowledgment)
                $model->update(['status' => 'delivered']);

                // Physically deduct stock now
                $this->deductRequisitionItemsFromStock($model);

                // Notify requestor to acknowledge
                $this->notifyRequestorOfDelivery($model);
            }
        }
    }

    /**
     * Creates a new event record in the approval_details table.
     */
    private function createEvent(Model $model, int $entityId, int $serial, string $status, ?int $fromId, ?int $toId, ?string $comments = null): void
    {
        $attributes = [
            'approval_entity_id' => $entityId,
            'serial' => $serial,
            'status' => $status,
            'from_user_id' => $fromId,
            'to_user_id' => $toId,
            'comments' => $comments,
        ];

        $text = "New Approval Task";
        $url = "#";

        if ($model instanceof Lot) {
            $attributes['lot_id'] = $model->id;
            $text = "Approval Pending for Lot #" . $model->lot_number;
            $url = route('lots.show', $model->id);
        } elseif ($model instanceof Requisition) {
            $attributes['requistion_id'] = $model->id;
            $text = "Approval Pending for Requisition #" . $model->id;
            $url = route('requisitions.pending');
        }

        ApprovalDetail::create($attributes);

        // 5. NOTIFY APPROVER (Only if there is a specific target user and it is a pending task)
        if ($toId && $status === 'pending') {
            $this->notificationService->createNotification(
                $toId,          // To User (The Approver)
                $text,          // Notification Text
                $url,           // Link to view the item
                $fromId         // From User (The person who triggered this step)
            );
        }
    }

    /**
     * Helper to notify the original requestor if their request was rejected.
     */
    private function notifyRequestorOfRejection(Model $model)
    {
        $requestorId = null;
        $text = "";
        $url = "#";

        if ($model instanceof Lot) {
            $requestorId = $model->created_by;
            $text = "Your Lot #" . $model->lot_number . " has been REJECTED.";
            $url = route('lots.show', $model->id);
        } elseif ($model instanceof Requisition) {
            $requestorId = $model->requested_by_user_id;
            $text = "Your Requisition #" . $model->id . " has been REJECTED.";
            $url = route('requisitions.pending');
        }

        if ($requestorId) {
            $this->notificationService->createNotification(
                $requestorId,
                $text,
                $url,
                Auth::id() // Rejected by current user
            );
        }
    }

    /**
     * Helper to notify the original requestor upon final approval.
     */
    private function notifyRequestorOfApproval(Model $model)
    {
        $requestorId = null;
        $text = "";
        $url = "#";

        if ($model instanceof Lot) {
            $requestorId = $model->created_by;
            $text = "Your Lot #" . $model->lot_number . " has been fully APPROVED.";
            $url = route('lots.show', $model->id);
        } elseif ($model instanceof Requisition) {
            $requestorId = $model->requested_by_user_id;
            $text = "Your Requisition #" . $model->id . " has been fully APPROVED/PROCESSED.";
            $url = route('requisitions.pending');
        }

        if ($requestorId) {
            $this->notificationService->createNotification(
                $requestorId,
                $text,
                $url,
                Auth::id() // The final approver
            );
        }
    }

    /**
     * Marks other pending tasks at the same level as 'superceded'.
     */
    private function supercedeOtherTasks(Model $model, int $entityId, int $serial): void
    {
        $query = ApprovalDetail::where('approval_entity_id', $entityId)
            ->where('serial', $serial)
            ->where('status', 'pending');

        if ($model instanceof Lot) {
            $query->where('lot_id', $model->id);
        } elseif ($model instanceof Requisition) {
            $query->where('requistion_id', $model->id);
        }

        $query->update(['status' => 'superceded']);
    }

    /**
     * Get all pending tasks assigned to a specific user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingTasksForUser(User $user)
    {
        // Subquery to find the max serial for each lot
        $lotSubQuery = DB::table('approval_details')
            ->select('lot_id', DB::raw('MAX(serial) as max_serial'))
            ->whereNotNull('lot_id')
            ->groupBy('lot_id');

        // Subquery to find the max serial for each requisition
        $reqSubQuery = DB::table('approval_details')
            ->select('requistion_id', DB::raw('MAX(serial) as max_serial'))
            ->whereNotNull('requistion_id')
            ->groupBy('requistion_id');

        return ApprovalDetail::query()
            // 1. APPLY THE JOINS AT THE TOP LEVEL
            ->leftJoinSub($lotSubQuery, 'lot_max', function ($join) {
                $join->on('approval_details.lot_id', '=', 'lot_max.lot_id');
            })
            ->leftJoinSub($reqSubQuery, 'req_max', function ($join) {
                $join->on('approval_details.requistion_id', '=', 'req_max.requistion_id');
            })

            // 2. NOW, FILTER THE RESULTS
            ->where('approval_details.to_user_id', $user->id)
            ->where('approval_details.status', 'pending')

            // 3. APPLY THE LOGIC TO FIND THE CURRENT STEP
            ->where(function ($query) {
                // The task is a Lot task AND its serial matches the max serial for that lot
                $query->where(function ($q) {
                    $q->whereNotNull('approval_details.lot_id')
                        ->whereColumn('approval_details.serial', '=', 'lot_max.max_serial');
                })
                    // OR the task is a Requisition task AND its serial matches the max serial for that requisition
                    ->orWhere(function ($q) {
                    $q->whereNotNull('approval_details.requistion_id')
                        ->whereColumn('approval_details.serial', '=', 'req_max.max_serial');
                });
            })

            ->with(['lot', 'requisition', 'fromUser']) // Eager load relationships
            ->select('approval_details.*') // Select only columns from the main table
            ->get();
    }

    /**
     * STOCK IN: Add all items from an approved lot to the stock table.
     */
    private function addLotItemsToStock(Lot $lot): void
    {
        $lot->load('lotDetails');

        foreach ($lot->lotDetails as $detail) {
            // Create a new stock record for each lot detail item
            // This assumes your BaseRepository's create() method handles 'created_by'
            $this->stockRepository->create([
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'lot_id' => $lot->id,
                'brand' => $detail->brand,
                'specification' => $detail->specification
            ]);
        }
    }

    /**
     * STOCK OUT: Deduct items from stock using FIFO logic.
     * This creates negative stock entries linked to specific lots.
     */
    private function deductRequisitionItemsFromStock(Requisition $requisition): void
    {
        $requisition->load('items');

        foreach ($requisition->items as $item) {
            // $this->consumeStockFifo($item->product_id, $item->quantity, $requisition);
            // New update -> Store Admin can now select specific lot for each item
            $this->consumeStock($item->product_id, $item->quantity, $requisition, $item);
        }
    }

    private function consumeStock($productId, $quantityNeeded, Requisition $requisition, $reqItem)
    {
        // 1. Get the Specific Lot Detail assigned
        $lotDetail = \App\Models\LotDetail::find($reqItem->lot_detail_id);

        if (!$lotDetail) {
            $productName = \App\Models\Product::find($productId)->name ?? 'Item';
            throw new Exception("Product '{$productName}' has no assigned Brand/Spec (Lot Detail ID missing).");
        }

        // 2. Check Balance in Stock (using the Brand/Spec from the Lot Detail)
        $currentBalance = Stock::where('product_id', $productId)
            ->where('lot_id', $lotDetail->lot_id)
            ->where('brand', $lotDetail->brand)
            ->where('specification', $lotDetail->specification)
            ->sum('quantity');

        if ($currentBalance < $quantityNeeded) {
            $productName = \App\Models\Product::find($productId)->name ?? 'Item';
            throw new Exception("Insufficient stock in the selected Lot for '{$productName}'. Available: {$currentBalance}, Requested: {$quantityNeeded}.");
        }

        // 3. Deduct
        $this->stockRepository->create([
            'product_id' => $productId,
            'quantity' => -($quantityNeeded),
            'lot_id' => $lotDetail->lot_id,
            'brand' => $lotDetail->brand,           // Taken from LotDetail
            'specification' => $lotDetail->specification // Taken from LotDetail
        ]);

        IssuedItem::create([
            'user_id' => $requisition->requested_by_user_id,
            'requisition_id' => $requisition->id,
            'product_id' => $productId,
            'lot_id' => $lotDetail->lot_id,
            'lot_detail_id' => $lotDetail->id,
            'quantity' => $quantityNeeded,
            'issued_date' => now(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    private function notifyRequestorOfDelivery(Model $model)
    {
        $requestorId = $model->requested_by_user_id;
        $text = "Your Requisition #" . $model->id . " has been DELIVERED. Please acknowledge receipt.";
        $url = route('requisitions.index'); // User goes here to acknowledge

        if ($requestorId) {
            $this->notificationService->createNotification($requestorId, $text, $url, Auth::id());
        }
    }

    /**
     * Calculate cost per category based on Lot items and update budget spent amount.
     * Throws exception if budget is exceeded or not found.
     */
    private function processBudgetForLot(Lot $lot): void
    {
        // Load product categories to group costs
        $lot->load('lotDetails.product.category');

        $fiscalYearId = $lot->fiscal_year_id;
        if (!$fiscalYearId) {
            throw new Exception("Lot #{$lot->lot_number} does not have a Fiscal Year assigned. Cannot process budget.");
        }

        // Group details by Category ID to sum costs (Price)
        $categoryCosts = [];

        foreach ($lot->lotDetails as $detail) {
            $catId = $detail->product->category_id;
            $cost = $detail->unit_price;

            if (!isset($categoryCosts[$catId])) {
                $categoryCosts[$catId] = 0;
            }
            $categoryCosts[$catId] += $cost;
        }
        // Process each category involved in this Lot
        foreach ($categoryCosts as $catId => $totalCost) {
            // Find the specific Budget record for this Category + Fiscal Year
            // We use lockForUpdate() to prevent race conditions during concurrent approvals
            $budget = \App\Models\Budget::where('category_id', $catId)
                ->where('fiscal_year_id', $fiscalYearId)
                ->lockForUpdate()
                ->first();

            if (!$budget) {
                $catName = \App\Models\ProductCategory::find($catId)->name ?? 'Unknown';
                throw new Exception("No budget defined for Category: '{$catName}' in this Fiscal Year. Cannot approve Lot.");
            }

            // Check Availability
            $available = $budget->amount - $budget->spent_amount;

            if ($totalCost > $available) {
                $catName = $budget->category->name;
                $formattedCost = number_format($totalCost, 2);
                $formattedAvail = number_format($available, 2);
                throw new Exception("Budget exceeded for '{$catName}'. Cost: {$formattedCost}, Available: {$formattedAvail}.");
            }
            // Deduct (Update Spent Amount)
            $budget->increment('spent_amount', $totalCost);
        }
    }

    /**
     * DEDUCT FROM PURCHASE TYPE BUDGET
     * Calculates the total value of the lot and deducts it from the specific Purchase Type Budget.
     *
     * @param Lot $lot
     * @throws Exception
     */
    private function processPurchaseTypeBudgetForLot(Lot $lot): void
    {
        $fiscalYearId = $lot->fiscal_year_id;
        $purchaseTypeId = $lot->product_purchase_type_id;

        // 1. Validation: Ensure Lot has necessary metadata
        if (!$fiscalYearId) {
            throw new Exception("Lot #{$lot->lot_number} does not have a Fiscal Year assigned. Cannot process Purchase Type budget.");
        }
        if (!$purchaseTypeId) {
            throw new Exception("Lot #{$lot->lot_number} does not have a Purchase Type assigned.");
        }

        // 2. Calculate Total Cost of the Lot
        // Note: Purchase Type Budget applies to the TOTAL value of the lot (sum of all items)
        $totalLotCost = 0;
        foreach ($lot->lotDetails as $detail) {
            $totalLotCost += $detail->unit_price;
        }

        // If cost is 0, no need to deduct
        if ($totalLotCost <= 0) {
            return;
        }

        // 3. Find the specific Purchase Type Budget record
        // Use lockForUpdate() to prevent race conditions during concurrent approvals
        $ptBudget = \App\Models\PurchaseTypeBudget::where('pt_id', $purchaseTypeId)
            ->where('fiscal_year_id', $fiscalYearId)
            ->lockForUpdate()
            ->first();

        if (!$ptBudget) {
            $ptName = $lot->purchaseType->name ?? 'Unknown';
            throw new Exception("No budget defined for Purchase Type: '{$ptName}' in this Fiscal Year.");
        }

        // 4. Check Availability
        $available = $ptBudget->amount - $ptBudget->spent_amount;

        if ($totalLotCost > $available) {
            $ptName = $ptBudget->PurchaseType->name;
            $formattedCost = number_format($totalLotCost, 2);
            $formattedAvail = number_format($available, 2);
            throw new Exception("Purchase Type Budget exceeded for '{$ptName}'. Cost: {$formattedCost}, Available: {$formattedAvail}.");
        }

        // 5. Deduct (Update Spent Amount)
        $ptBudget->increment('spent_amount', $totalLotCost);
    }

    /**
     * Notify Requestor that the Requisition is approved and Slip is ready to print.
     */
    private function notifyRequestorReadyForPrint(Requisition $requisition)
    {
        $requestorId = $requisition->requested_by_user_id;
        $text = "Requisition #{$requisition->id} Approved. Please print your invoice and collect your items.";
        $url = route('requisitions.index');

        if ($requestorId) {
            $this->notificationService->createNotification($requestorId, $text, $url, Auth::id());
        }
    }
}
