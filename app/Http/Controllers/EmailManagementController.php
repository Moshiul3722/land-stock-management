<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailManagement;
use App\Http\Controllers\AppBaseController;
use App\Repositories\EmailManagementRepository;
use App\Services\EmailService;

class EmailManagementController extends AppBaseController
{
    private $emailManagementRepository;
    private $emailService;

    public function __construct(EmailManagementRepository $emailManagementRepo, EmailService $emailService)
    {
        $this->emailManagementRepository = $emailManagementRepo;
        $this->emailService = $emailService;
    }

    public function index(Request $request)
    {
        $emailManagements = $this->emailManagementRepository->paginate(10);

        return view('email_managements.index')
            ->with('emailManagements', $emailManagements);
    }

    public function toggleActive($id)
    {
        $emailManagement = EmailManagement::findOrFail($id);
        $emailManagement->is_active = !$emailManagement->is_active;
        
        // Update status based on is_active value
        $emailManagement->status = $emailManagement->is_active ? '1' : '0';
    
        $emailManagement->save();
    
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'module_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);
    
        try {
            $emailManagement = new EmailManagement();
            $emailManagement->module_name = $request->input('module_name'); 
            $emailManagement->is_active = $request->input('is_active');
            
            // Set the status based on is_active value
            $emailManagement->status = $request->input('is_active') ? 1 : 0;
    
            $emailManagement->save();
    
            return response()->json($emailManagement);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}
