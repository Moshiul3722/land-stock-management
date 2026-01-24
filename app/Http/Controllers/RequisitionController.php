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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = $this->productCategoryService->fetchAll();
        return view('requisition.create', compact('categories'));
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

    public function index(Request $request)
    {
        $statuses = ['draft', 'pending', 'approved', 'rejected', 'processed'];

        if ($request->ajax()) {
            $filters = $request->only(['status', 'start_date', 'end_date']);
            $requisitions = $this->requisitionService->fetchAllForUser(Auth::user(), $filters);
            return view('requisition._my_list', compact('requisitions'));
        }

        return view('requisition.index', compact('statuses'));
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
        try {
            $this->requisitionService->approveRequisition($requisition, $request->input('comments'));
            return redirect()->route('requisitions.pending')->with('success', 'Requisition approved successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a requisition.
     */
    public function reject(Request $request, Requisition $requisition)
    {
        try {
            $this->requisitionService->rejectRequisition($requisition, $request->input('comments'));
            return redirect()->route('requisitions.pending')->with('success', 'Requisition rejected successfully.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
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
        foreach ($requisition->items as $item) {
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
    public function printSlip(Requisition $requisition)
    {
        // Load all necessary relationships for the slip
        $requisition->load([
            'requestedBy.department', // To show user details
            'items.product.category',
            'items.lot' // To show assigned lot/brand info
        ]);

        return view('requisition.print', compact('requisition'));
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
        if ($requisition->requested_by_user_id !== Auth::id()) {
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
        if (Auth::user()->role_id != 4) {
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
