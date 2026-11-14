<?php

namespace App\Http\Controllers;

use App\Services\ProductCategoryService;
use App\Services\RequisitionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Requisition;
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\DB;
use Flash;
use InvalidArgumentException;

class RequisitionController extends Controller
{
    protected $requisitionService;
    protected $productCategoryService;
    protected $stockService;

    public function __construct(
        RequisitionService $requisitionService,
        ProductCategoryService $productCategoryService,
        StockService $stockService
    ) {
        $this->requisitionService = $requisitionService;
        $this->productCategoryService = $productCategoryService;
        $this->stockService = $stockService;
    }

    /**
     * Get create form via AJAX for modal.
     */
    public function createAjax()
    {
        $categories = $this->productCategoryService->fetchAll();
        return view('requisition._form_modal', compact('categories'))->render();
    }

    /**
     * Get edit form via AJAX for modal.
     */
    public function editAjax(Requisition $requisition)
    {
        if ($requisition->status !== 'draft') {
            return response()->json(['error' => 'This requisition cannot be edited.'], 403);
        }

        $categories = $this->productCategoryService->fetchAll();
        $requisition->load('items.product.category');

        return view('requisition._form_modal', compact('requisition', 'categories'))->render();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $action = $request->input('action', 'submit');

            // Add requisition date to the request data
            $data = $request->all();
            $data['requisition_date'] = now()->format('Y-m-d');

            $requisition = $this->requisitionService->store($data, $action);

            $message = ($action === 'save_draft')
                ? 'Requisition saved as draft successfully.'
                : 'Requisition submitted for approval successfully.';

            return redirect()->route('requisitions.index')->with('success', $message)->with('new_requisition_id', $requisition->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Requisition management screen (Livewire: App\Livewire\Requisitions\ManageRequisitions).
     * The AJAX branch is kept for any caller of the previous jQuery list.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->hasHeader('X-Livewire')) {
            $filters = $request->only(['status', 'start_date', 'end_date']);
            $requisitions = $this->requisitionService->fetchAllForUser(Auth::user(), $filters);
            return view('requisition._my_list', compact('requisitions'));
        }

        return view('requisition.manage');
    }

    /**
     * Sidebar "Create Requisition" menu: opens the create modal on the management screen.
     */
    public function create()
    {
        return redirect()->route('requisitions.index', ['create' => 1]);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Requisition $requisition)
    {
        if ($requisition->status !== 'draft') {
            return redirect()->route('requisitions.index')->with('error', 'This requisition cannot be edited.');
        }

        $categories = $this->productCategoryService->fetchAll();
        $requisition->load('items.product.category');

        // Pre-calculate stock status for existing items so the view renders correctly
        foreach ($requisition->items as $item) {
            $stockSummary = $this->stockService->getAggregatedStockList(['product_id' => $item->product_id]);
            $qty = $stockSummary->first()->total_quantity ?? 0;

            $item->stock_status_label = $qty > 0 ? 'Available' : 'Not Available';
            $item->stock_status_class = $qty > 0 ? 'badge bg-success' : 'badge bg-danger';
        }

        return view('requisition.edit', compact('requisition', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requisition $requisition)
    {
        try {
            $action = $request->input('action', 'submit');
            $this->requisitionService->update($request->all(), $requisition->id, $action);

            $message = ($action === 'save_draft') ? 'Requisition draft updated.' : 'Requisition submitted successfully.';
            return redirect()->route('requisitions.index')->with('success', $message);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    /**
     * Display pending requisitions for the approver.
     */
    public function pending(Request $request)
    {
        $pendingTasks = $this->requisitionService->getPendingRequisitionsForUser(Auth::user());
        return view('requisition.pending', compact('pendingTasks'));
    }

    /**
     * Approve a requisition.
     */
    public function approve(Request $request, Requisition $requisition)
    {
        if (! $this->isWaitingForCurrentUser($requisition)) {
            return $this->backToDecisionPage()->with('error', __('requisition.pending_manage.modal.not_your_turn'));
        }

        try {
            $this->requisitionService->approveRequisition($requisition, $request->input('comments'));
            return $this->backToDecisionPage()->with('success', __('requisition.pending_manage.modal.approved'));
        } catch (InvalidArgumentException $e) {
            // CATCHES OUR CUSTOM VALIDATION ERROR AND SENDS IT TO THE UI
            Log::error($e->getMessage());
            Flash::error($e->getMessage());
            return redirect()->back();

        } catch (\Exception $e) {
            // Catches any other unexpected system errors
            Log::error($e->getMessage());
            Flash::error('An unexpected error occurred: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Reject a requisition.
     */
    public function reject(Request $request, Requisition $requisition)
    {
        if (! $this->isWaitingForCurrentUser($requisition)) {
            return $this->backToDecisionPage()->with('error', __('requisition.pending_manage.modal.not_your_turn'));
        }

        try {
            $this->requisitionService->rejectRequisition($requisition, $request->input('comments'));
            return $this->backToDecisionPage()->with('success', __('requisition.pending_manage.modal.rejected'));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    /**
     * The approver a pending step is assigned to may decide on it. Super Admin and Admin have
     * full access: they may decide on any requisition that is waiting at a step.
     */
    /**
     * After a decision the user returns to the page it was made on: the requisition list when
     * the dialog was opened there, otherwise the requisition request list.
     */
    private function backToDecisionPage()
    {
        $previous = (string) url()->previous();
        $path = (string) parse_url($previous, PHP_URL_PATH);

        return rtrim($path, '/') === parse_url(route('requisitions.index'), PHP_URL_PATH)
            ? redirect()->to($previous)
            : redirect()->route('requisitions.pending');
    }

    private function isWaitingForCurrentUser(Requisition $requisition): bool
    {
        $pending = $requisition->approvalDetails()->where('status', 'pending');

        if ($this->requisitionService->hasFullAccess(Auth::user())) {
            return $pending->exists();
        }

        return $pending->where('to_user_id', Auth::id())->exists();
    }

    /**
     * Get requisition details for the modal (AJAX).
     */
    public function getDetails(Requisition $requisition)
    {
        $requisition->load([
            'items.product.category',
            'items.product.subCategory',
            'requestedBy',
            'approvalDetails.fromUser.role',
            'approvalDetails.toUser.role',
            'approvalDetails.approvalEntity'
        ]);

        // 1. Fetch available lots (Brand/Spec) for each item
        // Replaces the old 'current_stock' calculation
        $lastReceipts = $this->lastReceiptsFor($requisition);

        foreach ($requisition->items as $item) {
            $item->previous_receipt = $lastReceipts->get($item->product_id);

            // Unit as shown on screen (Bangla name on Bangla pages)
            $item->product?->append('unit_label');

            $item->available_lots = DB::table('stocks')
                ->join('lots', 'stocks.lot_id', '=', 'lots.id')
                ->join('lot_details', function ($join) {
                    $join->on('stocks.lot_id', '=', 'lot_details.lot_id')
                        ->on('stocks.product_id', '=', 'lot_details.product_id')
                        // Ensure we match Brand/Spec exactly to get the right Detail ID
                        ->whereColumn('stocks.brand', 'lot_details.brand')
                        ->whereColumn('stocks.specification', 'lot_details.specification');
                })
                ->where('stocks.product_id', $item->product_id)
                ->whereNull('stocks.deleted_at')
                ->whereNull('lot_details.deleted_at')
                ->where('stocks.quantity', '>', 0) // Only show lots that have stock
                ->select(
                    'lots.id',
                    'lots.lot_number',
                    'lot_details.id as lot_detail_id',
                    'stocks.brand',
                    'stocks.specification',
                    'stocks.quantity'
                )
                ->distinct()
                ->get();

            // Current balance of the product over all lots (the dialog marks items that are out of stock).
            $item->available_quantity = (int) DB::table('stocks')
                ->where('product_id', $item->product_id)
                ->whereNull('deleted_at')
                ->sum('quantity');
        }

        // 2. Timeline Logic
        $timeline = collect([]);

        // Reconstruct "Step 0" (Initiated) logic
        $firstLog = $requisition->approvalDetails->sortBy('id')->first();
        $initiationComments = $requisition->remarks ?? ($firstLog ? $firstLog->comments : '');

        $timeline->push([
            'step' => 0,
            'status' => 'Initiated',
            'title' => 'Request Initiated',
            'user' => $requisition->requestedBy->name ?? 'Unknown',
            'role' => 'Requestor',
            'date' => $requisition->created_at->format('d M, Y h:i A'),
            'date_iso' => $requisition->created_at->toIso8601String(), // lets the page show the date in its own language
            'comments' => $initiationComments,
            'color' => 'primary',
            'status_raw' => 'initiated'
        ]);

        // Process Actual Workflow Steps
        $groupedDetails = $requisition->approvalDetails
            ->where('status', '!=', 'superceded')
            ->sortBy('serial')
            ->groupBy('serial');

        foreach ($groupedDetails as $serial => $details) {
            $actor = $details->first(function ($d) {
                return in_array($d->status, ['approved', 'rejected', 'processed']);
            });

            if ($actor) {
                $timeline->push([
                    'step' => $serial,
                    'status' => ucfirst($actor->status),
                    'title' => ucfirst($actor->status),
                    'user' => $actor->fromUser->name ?? 'System',
                    'role' => $actor->fromUser->role->role_name_en ?? 'Approver',
                    'date' => $actor->updated_at->format('d M, Y h:i A'),
                    'date_iso' => $actor->updated_at->toIso8601String(),
                    'comments' => $actor->comments,
                    'color' => $actor->status == 'rejected' ? 'danger' : 'success',
                    'status_raw' => $actor->status
                ]);
            } else {
                $pendingUsers = $details->map(function ($d) {
                    return $d->toUser->name ?? 'Unknown';
                })->unique()->join(', ');

                $roleName = $details->first()->toUser->role->role_name_en ?? 'Approver';

                $timeline->push([
                    'step' => $serial,
                    'status' => 'Pending',
                    'title' => 'Pending Approval',
                    'user' => 'Waiting for: ' . $pendingUsers,
                    'role' => $roleName,
                    'date' => $details->first()->created_at->format('d M, Y h:i A'),
                    'date_iso' => $details->first()->created_at->toIso8601String(),
                    'pending_users' => $pendingUsers,
                    'comments' => '',
                    'color' => 'warning',
                    'status_raw' => 'pending'
                ]);
            }
        }

        return response()->json([
            'requisition' => $requisition,
            'is_print_enabled' => $requisition->is_print_enabled,
            'requestor' => $requisition->requestedBy->name,
            'formatted_date' => $requisition->requisition_date->format('d M, Y'),
            'date_iso' => $requisition->requisition_date->format('Y-m-d'),
            'timeline' => $timeline
        ]);
    }

    /**
     * Update item quantities AND assigned lots.
     */
    public function updateItems(Request $request, Requisition $requisition)
    {
        try {
            // items is an array where key is itemId and value is an array of properties
            // Example: items[101] = { quantity: 5, lot_id: 12 }
            $items = $request->input('items', []);

            foreach ($items as $itemId => $data) {
                // Validate existence and relationship security
                $reqItem = RequisitionItem::where('id', $itemId)
                    ->where('requisition_id', $requisition->id)
                    ->first();

                // if ($reqItem) {
                //     // 1. Handle Quantity
                //     $qty = isset($data['quantity']) && $data['quantity'] > 0 ? $data['quantity'] : $reqItem->quantity;

                //     // 2. Handle Lot ID (Sanitize empty strings to NULL)
                //     $lotId = !empty($data['lot_id']) ? $data['lot_id'] : null;

                //     $reqItem->update([
                //         'quantity' => $qty,
                //         'lot_id' => $lotId
                //     ]);
                // }
                if ($reqItem) {
                    $updates = ['quantity' => $data['quantity']];

                    // If Lot Detail ID is sent (Store Admin selected it)
                    if (!empty($data['lot_detail_id'])) {
                        $updates['lot_detail_id'] = $data['lot_detail_id'];

                        // We can also auto-fill the main lot_id for convenience
                        $lotDetail = \App\Models\LotDetail::find($data['lot_detail_id']);
                        if ($lotDetail) {
                            $updates['lot_id'] = $lotDetail->lot_id;
                        }
                    }

                    $reqItem->update($updates);
                }
            }
            return redirect()
                ->route('requisitions.index')
                ->with('success', 'Requisition updated successfully.');
            // return response()->json(['success' => true, 'message' => 'Requisition updated successfully.']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Remove selected items from a requisition that is waiting for the current user's decision.
     *
     * Only the Store Admin (role 3) may do this, only while the requisition is pending with
     * them, and at least one item has to stay (a requisition without items is rejected instead).
     * Items are soft-deleted, so nothing is lost for the record.
     */
    public function removeItems(Request $request, Requisition $requisition)
    {
        $user = Auth::user();
        $waitingForUser = $this->isWaitingForCurrentUser($requisition);

        // The Store Admin at their own step; Super Admin / Admin at any step (full access).
        $mayRemove = $this->requisitionService->hasFullAccess($user)
            || $this->requisitionService->actingRoleFor($user, $requisition) === 3;

        if (! $mayRemove || ! $waitingForUser) {
            return response()->json(['success' => false, 'message' => __('requisition.pending_manage.modal.delete_not_allowed')], 403);
        }

        $ids = collect($request->input('item_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $items = RequisitionItem::where('requisition_id', $requisition->id)->whereIn('id', $ids)->get();

        if ($ids->isEmpty() || $items->count() !== $ids->count()) {
            return response()->json(['success' => false, 'message' => __('requisition.pending_manage.modal.delete_none')], 422);
        }

        if ($items->count() >= $requisition->items()->count()) {
            return response()->json(['success' => false, 'message' => __('requisition.pending_manage.modal.delete_all_blocked')], 422);
        }

        try {
            DB::transaction(function () use ($items, $user) {
                foreach ($items as $item) {
                    $item->forceFill(['deleted_by' => $user->id])->save();
                    $item->delete();
                }
            });
        } catch (Exception $e) {
            Log::error('Requisition item removal failed', ['requisition_id' => $requisition->id, 'exception' => $e]);

            return response()->json(['success' => false, 'message' => __('requisition.pending_manage.modal.action_failed')], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('requisition.pending_manage.modal.items_deleted', ['count' => $items->count()]),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requisition $requisition)
    {
        try {
            $this->requisitionService->destroy($requisition->id);
            return response()->json(['success' => true, 'message' => 'Requisition deleted successfully.']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update items (quantities) logic.
     */
    // OLD
    // public function updateItems(Request $request, Requisition $requisition)
    // {
    //     try {
    //         $items = $request->input('items', []);

    //         foreach ($items as $itemId => $quantity) {
    //             // Validate logic (optional): Ensure quantity is > 0
    //             if ($quantity > 0) {
    //                 RequisitionItem::where('id', $itemId)
    //                     ->where('requisition_id', $requisition->id) // Security check
    //                     ->update(['quantity' => $quantity]);
    //             }
    //         }

    //         return response()->json(['success' => true, 'message' => 'Quantities updated successfully.']);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
    //     }
    // }


    /**
     * Display the printable requisition slip with QR Code.
     */
    /**
     * When the requester last received each product of this requisition (through an earlier
     * requisition), and how many: the newest issue per product, keyed by product id.
     */
    private function lastReceiptsFor(Requisition $requisition)
    {
        return \App\Models\IssuedItem::query()
            ->where('user_id', $requisition->requested_by_user_id)
            ->where('requisition_id', '!=', $requisition->id)
            ->whereIn('product_id', $requisition->items->pluck('product_id'))
            ->whereNull('deleted_at')
            ->orderByDesc('issued_date')
            ->orderByDesc('id')
            ->get(['product_id', 'requisition_id', 'quantity', 'issued_date'])
            ->groupBy('product_id')
            ->map(function ($issues) {
                $latest = $issues->first();

                return [
                    'date_iso' => \Illuminate\Support\Carbon::parse($latest->issued_date)->format('Y-m-d'),
                    // everything received in that requisition for the product
                    'quantity' => (int) $issues->where('requisition_id', $latest->requisition_id)->sum('quantity'),
                    'requisition_id' => (int) $latest->requisition_id,
                ];
            });
    }

    public function printSlip(Requisition $requisition)
    {
        // Load all necessary relationships for the slip
        $requisition->load([
            'requestedBy.department', // To show user details
            'items.product.category',
            'items.lot' // To show assigned lot/brand info
        ]);

        // Each product's last receipt by the requester (date and quantity)
        $lastReceipts = $this->lastReceiptsFor($requisition);

        // The officer who approved it: the highest approval step before the store keeper's delivery
        $approval = \App\Models\ApprovalDetail::query()
            ->where('requistion_id', $requisition->id)
            ->where('status', 'approved')
            ->where('serial', '<', 3)
            ->orderByDesc('serial')
            ->orderByDesc('id')
            ->with('fromUser')
            ->first();
        $approver = $approval?->fromUser;
        $approverRole = $approver ? \App\Models\CnfRole::find($approver->role_id) : null;
        $approvedAt = $approval?->updated_at;

        return view('requisition.print', compact('requisition', 'lastReceipts', 'approver', 'approverRole', 'approvedAt'));
    }

    /**
     * User acknowledges receipt of items.
     * Transitions status from 'delivered' -> 'completed'.
     */
    public function acknowledge(Requisition $requisition)
    {
        // 1. Validation: Ensure it is in the correct state
        if ($requisition->status !== 'delivered') {
            return redirect()->back()->with('error', 'This requisition is not ready for acknowledgment.');
        }

        // 2. Security: Ensure the logged-in user is the actual requestor
        if ((int)$requisition->requested_by_user_id !== (int)Auth::id()) {
            // dd([
            //     'My ID (Auth)' => Auth::id(),
            //     'My ID Type' => gettype(Auth::id()),
            //     'Requisition Owner ID' => $requisition->requested_by_user_id,
            //     'Owner ID Type' => gettype($requisition->requested_by_user_id),
            //     'Match Status' => 'FAILED'
            // ]);
            return redirect()->back()->with('error', 'You are not authorized to acknowledge this requisition.');
        }

        try {
            // 3. Update Status
            $requisition->update([
                'status' => 'completed',
                // If you added a 'received_at' column to your table, update it here:
                // 'received_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Requisition acknowledged. Process completed successfully.');
        } catch (Exception $e) {
            Log::error('Acknowledgment Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error processing acknowledgment.');
        }
    }

    /**
     * Split selected items into a new requisition and notify Store Keeper.
     */
    public function split(Request $request, Requisition $requisition)
    {
        try {
            $itemIds = $request->input('item_ids', []);

            if (empty($itemIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected.'
                ], 400);
            }

            // Delegate logic to service
            // Returns Requisition object (if split) or null (if just notified)
            $newRequisition = $this->requisitionService->splitAndNotify($requisition, $itemIds);

            if ($newRequisition) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unavailable items moved to a new requisition (#' . $newRequisition->id . '). Store Keeper notified.'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Store Keeper notified to add stock for all items. No new requisition created.'
                ]);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a requisition via QR Scan (Store Keeper Only).
     * Validates, sets physical verification flag, and moves workflow to 'delivered'.
     */
    public function verifyAndProcess(Request $request, Requisition $requisition)
    {
        // 1. Authorization Check (Role 4 = Store Keeper)
        // Super Admin / Admin may do it too, while the requisition is at the Store Keeper step.
        if ($this->requisitionService->actingRoleFor(Auth::user(), $requisition) !== 4) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Store Keepers can scan.'], 403);
        }

        // 2. State Check (Prevent double processing)
        if ($requisition->status === 'delivered' || $requisition->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'This requisition has already been processed.'], 400);
        }

        try {
            // 3. Set Physical Verification Flag
            $requisition->update(['is_physically_verified' => true]);

            // 4. Trigger Approval Service to finalize
            // This reuses the existing approval logic. Since the current step is Store Keeper,
            // the service will automatically deduct stock and set status to 'delivered'.
            $this->requisitionService->approveRequisition($requisition, 'Physically Verified via QR Scan');

            return response()->json([
                'success' => true,
                'message' => 'Requisition #' . $requisition->id . ' verified and processed successfully.'
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
