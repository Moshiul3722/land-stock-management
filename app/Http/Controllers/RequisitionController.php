<?php

namespace App\Http\Controllers;

use App\Services\ProductCategoryService;
use App\Services\RequisitionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;

class RequisitionController extends Controller
{
    protected $requisitionService;
    protected $productCategoryService;

    public function __construct(
        RequisitionService $requisitionService,
        ProductCategoryService $productCategoryService
    ) {
        $this->requisitionService = $requisitionService;
        $this->productCategoryService = $productCategoryService;
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

    // Add index, show, edit, update, destroy methods later
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
}
