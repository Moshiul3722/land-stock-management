<?php

namespace App\Services;

use App\Repositories\ApprovalAuthorityRepository;
use App\Repositories\ApprovalRepository;
use App\Repositories\CnfRoleRepository;
use App\Repositories\DistrictsRepository;
use App\Repositories\FormMasterRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Exception;

class FormManagementService
{
    /** @var  UserRepository $userRepository*/
    private $userRepository;
    /** @var  FormMasterRepository $formMasterRepository*/
    private $formMasterRepository;
    /** @var   DistrictsRepository $districtsRepository*/
    private $districtsRepository;
    /** @var  ApprovalRepository $approvalRepository*/
    private $approvalRepository;
    /** @var  CnfRoleRepository $roleRepository*/
    private $roleRepository;
    /** @var  ApprovalAuthorityRepository $approvalAuthorityRepository*/
    private $approvalAuthorityRepository;
    /** @var  ApprovalService $approvalService*/
    private $approvalService;


    public function __construct(
        UserRepository $userRepository,
        FormMasterRepository $formMasterRepository,
        DistrictsRepository $districtsRepository,
        ApprovalRepository $approvalRepository,
        CnfRoleRepository $roleRepository,
        ApprovalAuthorityRepository $approvalAuthorityRepository,
        ApprovalService $approvalService
    ) {
        $this->userRepository = $userRepository;
        $this->formMasterRepository = $formMasterRepository;
        $this->districtsRepository = $districtsRepository;
        $this->approvalRepository = $approvalRepository;
        $this->roleRepository = $roleRepository;
        $this->approvalAuthorityRepository = $approvalAuthorityRepository;
        $this->approvalService = $approvalService;
    }
    public function getListViewData($formTypeId)
    {
        try {
            $user = Auth::user();
            $roleId = $user->role_id;
            if ($roleId == 2 || $roleId == 3 || $roleId == 5) {
                $files = $this->approvalService->getFilesInHandBigForms();
            } else {
                $files = $this->approvalService->getApprovedForms();
            }

            return $files;
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Error occured',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
    public function getListViewDataForAll()
    {
        try {
            $user = Auth::user();
            $roleId = $user->role_id;
            if ($roleId == 2 || $roleId == 3 || $roleId == 5) {
                $files = $this->approvalService->getFilesInHandBigForms();
            } else {
                $files = $this->approvalService->getApprovedForms();
            }
            return $files;
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Error occured',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
    public function getListViewDataTest()
    {
        try {
            $user = Auth::user();
            $roleId = $user->role_id;
            // $response = collect();

            if ($roleId == 2 || $roleId == 3 || $roleId == 5) {
                // Get current files and approved files for roles 2, 3, or 5
                $filesCurrent = $this->approvalService->getFilesInHandBigForms();
                $filesApproved = $this->approvalService->getApprovedFormsNew();
            } else {
                // Handle roles other than 2, 3, or 5 by returning an empty array for filesCurrent
                $filesCurrent = collect();  // Null array or empty collection
                $filesApproved = $this->approvalService->getApprovedFormsNew();
            }

            // // Push data into the response collection
            // $response->put('files_current', $filesCurrent);
            // $response->put('files_approved', $filesApproved);

            return response()->json([
                'success' => 'Query Successful',
                'filesCurrent' => $filesCurrent,
                'filesApproved' => $filesApproved,
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Error occurred',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
    public function getListViewDataApproved()
    {
        try {
            $filesApproved = $this->approvalService->getApprovedFormsNew();
            return response()->json($filesApproved, 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Error occurred',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
