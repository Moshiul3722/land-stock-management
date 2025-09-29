<?php

namespace App\Services;

use App\Repositories\CnfRoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\CnfPermissionRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PermissionService
{
    /** @var CnfRoleRepository $cnfRoleRepository*/
    private $cnfRoleRepository;
    /** @var UserRepository $userRepository*/
    private $userRepository;
    /** @var CnfPermissionRepository $cnfPermissionRepo*/
    private $cnfPermissionRepository;

    public function __construct(CnfRoleRepository $cnfRoleRepo, UserRepository $userRepo, CnfPermissionRepository $cnfPermissionRepo)
    {
        $this->cnfRoleRepository = $cnfRoleRepo;
        $this->userRepository = $userRepo;
        $this->cnfPermissionRepository = $cnfPermissionRepo;
    }

    /**
     * Get List of All Menus
     */
    public function getAllMenusByRole($roleId)
    {
        $permissions = $this->cnfPermissionRepository->getPermissionsWithMenusAndSubmodules($roleId);
        return response()->json($permissions);
    }

    /**
     * Get All Users of A Specific Role
     * Providing the roleId
     * isActive parameter is optional (by default it will be set to 1)
     */
    public function getAllUsersByRole($roleId, $isActive = null)
    {
        // Define the base search criteria
        $searchCriteria = [
            'role_id' => $roleId,
            'is_active' => 1
        ];

        // Add the 'is_active' condition if $isActive is not null
        if ($isActive !== null) {
            $searchCriteria['is_active'] = $isActive;
        }
        // Retrieve users matching the search criteria
        $userListByRole = $this->userRepository->all($searchCriteria);
        return response()->json($userListByRole);
    }

    /**
     * Save the Permissions to Database
     * Parameters - 1. Array of permission (menu_id & permission_id)
     */
    public function savePermission($permissions)
    {
        try {
            foreach ($permissions as $permission) {
                $menuId = $permission['menu_id'];
                $roleId = $permission['role_id'];
                $permissionId = $permission['permission_id'];

                // Define the base search criteria
                $searchCriteria = [
                    'role_id' => $roleId,
                    'menu_id' => $menuId
                ];

                // Retrieve users matching the search criteria
                $existingPermission = $this->cnfPermissionRepository->all($searchCriteria)->first();
                if ($existingPermission) {
                    // If permission_id is 0, apply soft delete; otherwise, do nothing
                    if ($permissionId == 0) {
                        $this->cnfPermissionRepository->delete($existingPermission->id);
                    }
                } else {
                    // Insert the menu_id and role_id via the CnfPermission model
                    if ($permissionId == 1) {
                        $this->cnfPermissionRepository->create([
                            'menu_id' => $menuId,
                            'role_id' => $roleId,
                        ]);
                    }
                }
            }
            return response()->json(['success' => 'Updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
