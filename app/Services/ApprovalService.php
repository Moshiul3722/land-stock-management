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

class ApprovalService
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

    public function __construct(
        UserRepository $userRepository,
        FormMasterRepository $formMasterRepository,
        DistrictsRepository $districtsRepository,
        ApprovalRepository $approvalRepository,
        CnfRoleRepository $roleRepository,
        ApprovalAuthorityRepository $approvalAuthorityRepository
    ) {
        $this->userRepository = $userRepository;
        $this->formMasterRepository = $formMasterRepository;
        $this->districtsRepository = $districtsRepository;
        $this->approvalRepository = $approvalRepository;
        $this->roleRepository = $roleRepository;
        $this->approvalAuthorityRepository = $approvalAuthorityRepository;
    }

    /**
     * To submit forms
     * Parameter - formMasterId, id of the form_master table
     * returns a success message for successful submission.
     */
    public function submit($formMasterId)
    {
        $user = Auth::user();
        $districtId = $user->district_id;
        // CO and CME must have district Id tagged
        if ($districtId) {
            if ($formMasterId) {
                $searchCriteria = [
                    'form_master_id' => $formMasterId,
                ];
                $approvalData = $this->approvalRepository->all($searchCriteria);

                if ($approvalData->isEmpty()) {
                    // New Submission
                    // Save as new record
                    // Status will be 1 = Submitted to CME by CO
                    $input = [
                        'form_master_id' => $formMasterId,
                        'user_from' => $user->id,
                        'district_id' => $districtId,
                        'status' => 1,
                        'is_active' => 1,
                    ];
                    $approvalData = $this->approvalRepository->create($input);
                    $input = [
                        'master_status' => 1
                    ];
                    $updateFormMaster = $this->formMasterRepository->update($input, $formMasterId);
                    return response()->json([
                        'success' => 'Saved',
                        'message' => 'Successfully Saved'
                    ], 200);
                } else {
                    // Already submitted before
                    // Finding the last status
                    $latestApprovalData = $approvalData->sortByDesc('created_at')->first();
                    $lastStatus = $latestApprovalData->status;
                    if ($lastStatus == 1) {
                        // It means it was submitted by CO to CME
                        // Now it will be submitted to MnE by CME
                        $status = 2;
                    } else if ($lastStatus == 2) {
                        // It means it was submitted by CME to MnE
                        // Now it will be approved by MnE
                        $status = 3;
                    } else {
                        // return error as no more statuses are available
                        return response()->json([
                            'error' => 'An error occurred while submitting data',
                            'message' => 'Invalid status'
                        ], 500);
                    }
                    $input = [
                        'form_master_id' => $formMasterId,
                        'user_from' => $user->id,
                        'district_id' => $districtId,
                        'status' => $status,
                        'is_active' => 1,
                    ];
                    $approvalData = $this->approvalRepository->create($input);
                    $input = [
                        'master_status' => $status
                    ];
                    $updateFormMaster = $this->formMasterRepository->update($input, $formMasterId);
                    $update = [
                        'is_active' => 0
                    ];
                    $updateLastApprovalStatus = $this->approvalRepository->update($update, $latestApprovalData->id);
                    return response()->json([
                        'success' => 'Saved',
                        'message' => 'Successfully Saved'
                    ], 200);
                }
            } else {
                return response()->json([
                    'error' => 'An error occurred while submitting data',
                    'message' => 'Invalid Parameter formMasterId'
                ], 500);
            }
        } else {
            // MnE will not have district Ids
            // So check for approval authority
            $searchCriteria = [
                'role_id' => $user->role_id
            ];
            $checkForApprovalAuthority = $this->approvalAuthorityRepository->all($searchCriteria);
            if ($checkForApprovalAuthority->isNotEmpty()) {
                $searchCriteria = [
                    'form_master_id' => $formMasterId,
                ];
                $approvalData = $this->approvalRepository->all($searchCriteria);
                $latestApprovalData = $approvalData->sortByDesc('created_at')->first();
                $input = [
                    'form_master_id' => $formMasterId,
                    'user_from' => $user->id,
                    'status' => 3,
                    'is_active' => 1,
                ];
                $approvalData = $this->approvalRepository->create($input);
                $update = [
                    'is_active' => 0
                ];
                $updateLastApprovalStatus = $this->approvalRepository->update($update, $latestApprovalData->id);
                $input = [
                    'master_status' => 3
                ];
                $updateFormMaster = $this->formMasterRepository->update($input, $formMasterId);
                return response()->json([
                    'success' => 'Saved',
                    'message' => 'Successfully Saved'
                ], 200);
            }
            return response()->json([
                'error' => 'User not permitted to submit',
                'message' => 'User doesn\'t have district tagged'
            ], 500);
        }
    }

    /**
     * getFilesInHand
     * to Get the list of files in a users hand
     */
    public function getFilesInHand()
    {
        $user = Auth::user();
        $districtId = $user->district_id;
        // CO and CME will have district Id tagged
        if ($districtId) {
            // FIles in their hand will have active status = 1 and matching district Ids

            // Get role to match with status
            if ($user->role_id == 2) {
                // He/She will not have any data in Approval Process because files submitted by him/her will be seen by CmE.
                // But he/she might have data in FormMaster table having status = 0
                $searchCriteria = [
                    'status' => '0',
                    'district_id' => $districtId
                ];
                $formMasterData = $this->formMasterRepository->all($searchCriteria);
                $response = collect();
                if ($formMasterData->isNotEmpty()) {
                    $count = $formMasterData->count();
                    foreach ($formMasterData as $data) {
                        $response->push($data->id);
                    }
                    return response()->json([
                        'success' => 'Data found',
                        'count' => $count,
                        'data' => $response,
                    ], 200);
                } else {
                    $response = collect();
                    return response()->json([
                        'success' => 'No Data found',
                        'count' => 0,
                        'data' => $response,
                    ], 200);
                }
            } else if ($user->role_id == 3) {
                $status = 1;
            }
            $searchCriteria = [
                'district_id' => $districtId,
                'status' => $status,
                'is_active' => 1
            ];
            $approvalData = $this->approvalRepository->all($searchCriteria);
            if ($approvalData->isNotEmpty()) {
                $count = $approvalData->count();
                $response = collect();
                foreach ($approvalData as $data) {

                    $searchCriteria = [
                        'id' => $data->form_master_id
                    ];
                    $formMasterData = $this->formMasterRepository->all($searchCriteria);
                    if ($formMasterData->isNotEmpty()) {
                        $formMasterData = $formMasterData->first();
                        $response->push($formMasterData->id);
                    }
                }
                return response()->json([
                    'success' => 'Data found',
                    'count' => $count,
                    'data' => $response,
                ], 200);
            } else {
                $response = collect();
                return response()->json([
                    'success' => 'No Data found',
                    'count' => 0,
                    'data' => $response,
                ], 200);
            }
        } else {
            // MnE will not have district Ids
            // So check for approval authority
            $searchCriteria = [
                'role_id' => $user->role_id
            ];
            $checkForApprovalAuthority = $this->approvalAuthorityRepository->all($searchCriteria);
            if ($checkForApprovalAuthority->isNotEmpty()) {
                $checkForApprovalAuthority = $checkForApprovalAuthority->sortByDesc('created_at')->first();
                $roleId = $checkForApprovalAuthority->role_id;
                if ($user->role_id == $roleId) {
                    $searchCriteria = [
                        'status' => 2,
                        'is_active' => 1
                    ];
                    $approvalData = $this->approvalRepository->all($searchCriteria);
                    if ($approvalData->isNotEmpty()) {
                        $count = $approvalData->count();
                        $response = collect();
                        foreach ($approvalData as $data) {

                            $searchCriteria = [
                                'id' => $data->form_master_id
                            ];
                            $formMasterData = $this->formMasterRepository->all($searchCriteria);
                            if ($formMasterData->isNotEmpty()) {
                                $formMasterData = $formMasterData->first();
                                $response->push($formMasterData->id);
                            }
                        }
                        return response()->json([
                            'success' => 'Data found',
                            'count' => $count,
                            'data' => $response,
                        ], 200);
                    } else {
                        $response = collect();
                        return response()->json([
                            'success' => 'No Data found',
                            'count' => 0,
                            'data' => $response,
                        ], 200);
                    }
                } else {
                    // User not permitted
                    return response()->json([
                        'error' => 'Unauthorized access',
                    ], 403);
                }
            }
        }
    }
    /**
     * getFilesInHandBigForms
     * to Get the list of files in a users hand for big forms
     */
    public function getFilesInHandBigForms()
    {
        $user = Auth::user();
        $districtId = $user->district_id;
        // CO and CME will have district Id tagged
        if ($districtId) {
            // FIles in their hand will have active status = 1 and matching district Ids
            // Get role to match with status
            if ($user->role_id == 2) {
                // dd("2");
                // He/She will not have any data in Approval Process because files submitted by him/her will be seen by CmE.
                $searchCriteria = [
                    // 'status' => '0',
                    'district_id' => $districtId
                ];
                $formMasterData = $this->formMasterRepository->all($searchCriteria);
                $response = collect();
                if ($formMasterData->isNotEmpty()) {
                    $count = $formMasterData->count();
                    foreach ($formMasterData as $data) {
                        $response->push($data->id);
                    }
                    return response()->json([
                        'success' => 'Data found',
                        'count' => $count,
                        'data' => $response,
                    ], 200);
                } else {
                    $response = collect();
                    return response()->json([
                        'success' => 'No Data found',
                        'count' => 0,
                        'data' => $response,
                    ], 200);
                }
            } else if ($user->role_id == 3) {
                // Check for files in his/her hands (status == 1)
                $status = 1;
                $searchCriteria = [
                    'district_id' => $districtId,
                    'status' => $status,
                    'is_active' => 1
                ];
                $approvalData = $this->approvalRepository->all($searchCriteria);
                $response = collect();
                if ($approvalData->isNotEmpty()) {
                    $count = $approvalData->count();
                    foreach ($approvalData as $data) {
                        $searchCriteria = [
                            'id' => $data->form_master_id
                        ];
                        $formMasterData = $this->formMasterRepository->all($searchCriteria);
                        if ($formMasterData->isNotEmpty()) {
                            $formMasterData = $formMasterData->first();
                            $response->push($formMasterData->id);
                        }
                    }
                }
                // Searching for data which are submitted by him/her
                $searchCriteria = [
                    'district_id' => $districtId,
                    'master_status' => 2
                ];
                $formMasterData = $this->formMasterRepository->all($searchCriteria);
                if ($formMasterData->isNotEmpty()) {
                    foreach ($formMasterData as $data) {
                        // push only if $data->id is not present in $response collection
                        if (!$response->contains($data->id)) {
                            $response->push($data->id);
                        }
                    }
                }
                // Return response
                if ($response->isEmpty()) {
                    // If $response is empty, return a response indicating no data found
                    $response = collect();
                    return response()->json([
                        'success' => 'No Data found',
                        'count' => 0,
                        'data' => $response, // Return an empty collection
                    ], 200);
                } else {
                    // If $response is not empty, count the items and return the data
                    $count = $response->count(); // Count the items in the response
                    return response()->json([
                        'success' => 'Data found',
                        'count' => $count,
                        'data' => $response,
                    ], 200);
                }
            }
        } else {
            // MnE will not have district Ids
            // So check for approval authority
            $searchCriteria = [
                'role_id' => $user->role_id
            ];
            $checkForApprovalAuthority = $this->approvalAuthorityRepository->all($searchCriteria);
            if ($checkForApprovalAuthority->isNotEmpty()) {
                $checkForApprovalAuthority = $checkForApprovalAuthority->sortByDesc('created_at')->first();
                $roleId = $checkForApprovalAuthority->role_id;
                if ($user->role_id == $roleId) {
                    $searchCriteria = [
                        'status' => 2,
                        'is_active' => 1
                    ];
                    $approvalData = $this->approvalRepository->all($searchCriteria);
                    if ($approvalData->isNotEmpty()) {
                        $count = $approvalData->count();
                        $response = collect();
                        foreach ($approvalData as $data) {

                            $searchCriteria = [
                                'id' => $data->form_master_id
                            ];
                            $formMasterData = $this->formMasterRepository->all($searchCriteria);
                            if ($formMasterData->isNotEmpty()) {
                                $formMasterData = $formMasterData->first();
                                $response->push($formMasterData->id);
                            }
                        }
                        return response()->json([
                            'success' => 'Data found',
                            'count' => $count,
                            'data' => $response,
                        ], 200);
                    } else {
                        $response = collect();
                        return response()->json([
                            'success' => 'No Data found',
                            'count' => 0,
                            'data' => $response,
                        ], 200);
                    }
                } else {
                    // User not permitted
                    return response()->json([
                        'error' => 'Unauthorized access',
                    ], 403);
                }
            }
        }
    }

    /**
     * Get the Approved forms
     * Regardless of the User/Role this function returns the list of forms
     */
    public function getApprovedForms()
    {
        try {
            $searchCriteria = [
                'status' => 3,
                'is_active' => 1
            ];
            $approvalData = $this->approvalRepository->all($searchCriteria);
            if ($approvalData->isNotEmpty()) {
                $count = $approvalData->count();
                $response = collect();
                foreach ($approvalData as $data) {
                    $searchCriteria = [
                        'id' => $data->form_master_id
                    ];
                    $formMasterData = $this->formMasterRepository->all($searchCriteria);
                    if ($formMasterData->isNotEmpty()) {
                        $formMasterData = $formMasterData->first();
                        $response->push($formMasterData->id);
                    }
                }
                return response()->json([
                    'success' => 'Data found',
                    'count' => $count,
                    'data' => $response,
                ], 200);
            } else {
                $response = collect();
                return response()->json([
                    'success' => 'No Data found',
                    'count' => 0,
                    'data' => $response,
                ], 200);
            }
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Error occured',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
    public function getApprovedFormsNew()
    {
        try {
            $user = Auth::user();
            $roleId = $user->role_id;
            $searchCriteria = [];
            if ($roleId == 2 || $roleId == 3) {
                $searchCriteria = [
                    'master_status' => 3,
                    'district_id' => $user->district_id
                ];
            } else {
                $searchCriteria = [
                    'master_status' => 3
                ];
            }
            $formMasterData = $this->formMasterRepository->all($searchCriteria);
            $response = collect();
            if ($formMasterData->isNotEmpty()) {
                $response = $formMasterData->pluck('id');
            }
            return response()->json($response, 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'Error occured',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
