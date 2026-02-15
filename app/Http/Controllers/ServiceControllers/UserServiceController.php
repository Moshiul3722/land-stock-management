<?php

namespace App\Http\Controllers\ServiceControllers;

use App\Http\Controllers\AppBaseController;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserServiceController extends AppBaseController
{
    private $roleService;
    /** @var UserService $userService*/
    private $userService;

    public function __construct(
        RoleService $roleService,
        UserService $userService
    ) {
        $this->roleService = $roleService;
        $this->userService = $userService;
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
     * Parameters - roleId, isActive (Will be 1 if not provided)
     * Method - POST
     * Route Name - getAllUsersByRole
     */
    public function getAllUsersByRole($roleId, $isActive = null)
    {
        try {
            if ($isActive == null) {
                $userListByRole = $this->roleService->getAllUsersByRole($roleId);
            } else {
                $userListByRole = $this->roleService->getAllUsersByRole($roleId, $isActive);
            }
            return response()->json($userListByRole);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Get All User Types
     */
    public function getAllUserTypes()
    {
        try {
            $userTypesList = $this->userService->getAllUserTypes();
            return response()->json($userTypesList);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create new User (Storing Data to the Users table)
     */
    public function create(Request $request)
    {
        try {
            $role = $this->userService->create($request);
            return response()->json($role);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Find a User based on the Id
     */
    public function find($userId)
    {
        try {
            $user = $this->userService->find($userId);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a User based on the Id
     */
    public function update($user, $userId)
    {
        try {
            $user = $this->userService->update($user, $userId);
            return $user;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
