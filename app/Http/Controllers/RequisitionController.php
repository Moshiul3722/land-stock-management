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

            $this->requisitionService->store($data, $action);

            $message = ($action === 'save_draft')
                ? 'Requisition saved as draft successfully.'
                : 'Requisition submitted for approval successfully.';

            return redirect()->route('requisitions.index')->with('success', $message); // Assuming index exists
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
            'requestedBy',
            'approvalDetails.fromUser.role',
            'approvalDetails.toUser.role',
        ]);

        foreach ($requisition->items as $item) {
            // Get aggregated stock for this specific product
            $stockSummary = $this->stockService->getAggregatedStockList(['product_id' => $item->product_id]);
            $item->current_stock = $stockSummary->first()->total_quantity ?? 0;
        }

        $timeline = collect([]);

        // 1. Reconstruct "Step 0" (Initiated) logic
        // We grab the very first log entry (lowest ID) to find the initiation details
        $firstLog = $requisition->approvalDetails->sortBy('id')->first();

        // Use the remarks from the main table, or fallback to the comment in the first log
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

        // 2. Process Actual Workflow Steps
        $groupedDetails = $requisition->approvalDetails
            ->where('status', '!=', 'superceded')
            ->sortBy('serial')
            ->groupBy('serial');

        foreach ($groupedDetails as $serial => $details) {
            // Check if anyone has acted (approved/rejected/processed)
            $actor = $details->first(function ($d) {
                return in_array($d->status, ['approved', 'rejected', 'processed']);
            });

            if ($actor) {
                // ACTED: Show the person who took action
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
                // PENDING: Show who we are waiting for
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
                    'comments' => '', // Pending steps don't have comments yet
                    'color' => 'warning',
                    'status_raw' => 'pending'
                ]);
            }
        }

        return response()->json([
            'requisition' => $requisition,
            'requestor' => $requisition->requestedBy->name,
            'formatted_date' => $requisition->requisition_date->format('d M, Y'),
            'timeline' => $timeline
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
    public function updateItems(Request $request, Requisition $requisition)
    {
        try {
            $items = $request->input('items', []);

            foreach ($items as $itemId => $quantity) {
                // Validate logic (optional): Ensure quantity is > 0
                if ($quantity > 0) {
                    RequisitionItem::where('id', $itemId)
                        ->where('requisition_id', $requisition->id) // Security check
                        ->update(['quantity' => $quantity]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Quantities updated successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }
}
