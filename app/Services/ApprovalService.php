<?php

namespace App\Services;

use App\Models\ApprovalDetail;
use App\Models\ApprovalEntity;
use App\Models\Lot;
use App\Models\Requisition;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApprovalService
{
    /**
     * Kicks off the approval workflow for a given model instance.
     *
     * @param Model $model The model instance (Lot or Requisition)
     * @param string $entityName The name of the process (e.g., "Lot Approval")
     */
    public function submitForApproval(Model $model, string $entityName): void
    {
        DB::transaction(function () use ($model, $entityName) {
            // Start the workflow from the beginning (before serial 1)
            $this->advanceWorkflow($model, $entityName, 0);
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

            // 1. Log the user's action (approved/rejected)
            $this->createEvent($model, $entity->id, $pendingTask->serial, $status, Auth::id(), null, $comments);

            // 2. Mark other pending tasks at this level as superceded
            $this->supercedeOtherTasks($model, $entity->id, $pendingTask->serial);

            // 3. If approved, try to advance to the next step. Otherwise, stop the workflow.
            if ($status === 'approved') {
                $this->advanceWorkflow($model, $entity->name, $pendingTask->serial);
            } else {
                $model->update(['status' => 'rejected']);
            }
        });
    }

    /**
     * Finds and advances the workflow to the next step based on the rules.
     */
    private function advanceWorkflow(Model $model, string $entityName, int $currentSerial): void
    {
        $entity = ApprovalEntity::where('name', $entityName)->firstOrFail();
        $nextStep = $entity->processes()->where('serial', $currentSerial + 1)->first();

        if ($nextStep) {
            $nextApprovers = User::where('role_id', $nextStep->role_id)->get();
            if ($nextApprovers->isEmpty()) {
                throw new Exception("No users found for the role required in the next approval step.");
            }

            foreach ($nextApprovers as $approver) {
                $this->createEvent($model, $entity->id, $nextStep->serial, 'pending', Auth::id(), $approver->id);
            }
            $model->update(['current_serial' => $nextStep->serial]);
        } else {
            // No more steps, the process is complete
            $model->update(['status' => 'approved']);
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

        if ($model instanceof Lot) {
            $attributes['lot_id'] = $model->id;
        } elseif ($model instanceof Requisition) {
            $attributes['requistion_id'] = $model->id;
        }

        ApprovalDetail::create($attributes);
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
            ->where('to_user_id', $user->id)
            ->where('status', 'pending')
            ->where(function ($query) use ($lotSubQuery, $reqSubQuery) {
                // Join with subqueries to ensure we only get tasks at the current step
                $query->leftJoinSub($lotSubQuery, 'lot_max', function ($join) {
                    $join->on('approval_details.lot_id', '=', 'lot_max.lot_id');
                })->where(function ($q) {
                    $q->whereNull('approval_details.lot_id')
                        ->orWhereColumn('approval_details.serial', '=', 'lot_max.max_serial');
                });

                $query->leftJoinSub($reqSubQuery, 'req_max', function ($join) {
                    $join->on('approval_details.requistion_id', '=', 'req_max.requistion_id');
                })->where(function ($q) {
                    $q->whereNull('approval_details.requistion_id')
                        ->orWhereColumn('approval_details.serial', '=', 'req_max.max_serial');
                });
            })
            ->with(['lot', 'requisition', 'fromUser']) // Eager load relationships
            ->select('approval_details.*') // Select only columns from the main table to avoid conflicts
            ->get();
    }
}
