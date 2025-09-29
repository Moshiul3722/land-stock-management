<?php

namespace App\Http\Controllers\ServiceControllers;

use App\Http\Controllers\AppBaseController;
use App\Services\RoleService;
use Symfony\Component\HttpFoundation\Request;

class RoleServiceController extends AppBaseController
{
    /** @var CnfBankRepository $cnfBankRepository*/
    private $roleService;

    public function __construct(
        RoleService $roleService
    ) {
        $this->roleService = $roleService;
    }

    /**
     * Get a JSON list of all Roles
     * Method - POST
     * Route Name - getAllRoles
     */
    public function getAllRoles()
    {
        try {
            $roleList = $this->roleService->getAllRoles();
            return response()->json($roleList);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a JSON list of users by Role Id
     * Parameters - role_id, is_active (Will be 1 if not provided)
     * Method - POST
     * Route Name - getAllUsersByRole
     */
    public function getAllUsersByRole(Request $request)
    {
        try {
            $roleId = $request->role_id;
            if(!isset($request->is_active)) {
                $userListByRole = $this->roleService->getAllUsersByRole($roleId);
            } else {
                $userListByRole = $this->roleService->getAllUsersByRole($roleId, 0);
            }
            return response()->json($userListByRole);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get a blade file of table of users by Role Id
     * Parameters - role_id, is_active (Will be 1 if not provided)
     * Method - POST
     * Route Name - getAllUsersByRole
     */
    public function getAllUsersByRoleBlade(Request $request)
    {
        try {
            $roleId = $request->role_id;
            if(!isset($request->is_active)) {
                $userListByRole = $this->roleService->getAllUsersByRole($roleId);
            } else {
                $userListByRole = $this->roleService->getAllUsersByRole($roleId, 0);
            }
            // dd($userListByRole);
            $userListByRole = $userListByRole->getOriginalContent();
            return view('users.table')
                ->with('userListByRole', $userListByRole);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
