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
            $nextApprovers = User::where('role_id', $nextStep->role_id)->get();
            if ($nextApprovers->isEmpty()) {
                throw new Exception("No users found for the role required in the next approval step.");
            }

            foreach ($nextApprovers as $approver) {
                $commentsToSave = ($currentSerial === 0) ? $initComments : null;
                $this->createEvent($model, $entity->id, $nextStep->serial, 'pending', Auth::id(), $approver->id, $commentsToSave);
            }
            $model->update(['current_serial' => $nextStep->serial]);
        } else {
            // No more steps, the process is complete
            $model->update(['status' => 'approved']);

            // If the approved model is a Lot, add its items to the stock.
            if ($model instanceof Lot) {
                // STOCK IN: Add items to stock
                $this->addLotItemsToStock($model);
            } elseif ($model instanceof Requisition) {
                // STOCK OUT: Deduct items using FIFO
                $this->deductRequisitionItemsFromStock($model);
            }
            // NOTIFY REQUESTOR ON APPROVAL
            $this->notifyRequestorOfApproval($model);
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
                'lot_id' => $lot->id
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
            $this->consumeStockFifo($item->product_id, $item->quantity, $requisition);
        }
    }

    /**
     * Helper to consume stock for a single product using FIFO.
     */
    private function consumeStockFifo($productId, $quantityNeeded, Requisition $requisition)
    {
        // 1. Find all Lots that have stock for this product, ordered by oldest entry date first (FIFO)
        // We group by lot_id and sum the quantity to find the CURRENT balance of that lot.
        $lotBalances = Stock::query()
            ->join('lots', 'stocks.lot_id', '=', 'lots.id')
            ->where('stocks.product_id', $productId)
            ->whereNull('stocks.deleted_at')
            ->select('stocks.lot_id', 'lots.entry_date', DB::raw('SUM(stocks.quantity) as balance'))
            ->groupBy('stocks.lot_id', 'lots.entry_date')
            ->having('balance', '>', 0)
            ->orderBy('lots.entry_date', 'asc')
            ->get();


        $remainingToDeduct = $quantityNeeded;

        foreach ($lotBalances as $lotData) {
            if ($remainingToDeduct <= 0) break;

            $amountToTake = min($remainingToDeduct, $lotData->balance);

            // A. WAREHOUSE UPDATE: Create NEGATIVE stock entry (Reduces Warehouse Stock)
            $this->stockRepository->create([
                'product_id' => $productId,
                'quantity' => - ($amountToTake),
                'lot_id' => $lotData->lot_id
            ]);

            // B. USER UPDATE: Create ISSUED ITEM entry (Increases User's Asset List)
            IssuedItem::create([
                'user_id' => $requisition->requested_by_user_id, // Mr. X
                'requisition_id' => $requisition->id,
                'product_id' => $productId, // Product Z
                'lot_id' => $lotData->lot_id, // The specific Lot
                'quantity' => $amountToTake, // Quantity Y (from this specific lot)
                'issued_date' => now(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $remainingToDeduct -= $amountToTake;
        }

        // Optional: Safety check
        if ($remainingToDeduct > 0) {
            // This implies a data race condition occurred where stock disappeared during approval.
            // You might want to log this or throw an exception.
            throw new Exception("Insufficient stock to fulfill requisition for Product ID: $productId");
        }
    }
}
