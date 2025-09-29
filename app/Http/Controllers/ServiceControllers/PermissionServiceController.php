<?php

namespace App\Http\Controllers\ServiceControllers;

use App\Http\Controllers\AppBaseController;
use App\Services\PermissionService;
use App\Services\RoleService;
use Symfony\Component\HttpFoundation\Request;

class PermissionServiceController extends AppBaseController
{
    /** @var PermissionService $permissionService*/
    private $permissionService;

    public function __construct(
        PermissionService $permissionService
    ) {
        // $this->roleService = $roleService;
        $this->permissionService = $permissionService;
    }

    /**
     * Get List of All Menus
     */
    public function getAllMenusByRole(Request $request)
    {
        try {
            // Retrieve role_id
            $roleId = $request->input('role_id');
            $menuItems = $this->permissionService->getAllMenusByRole($roleId);
            // return response()->json($permissions);

            return view('cnf_permissions.table')
                ->with('menuItems', $menuItems)
                ->with('roleId', $roleId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Save Permission
     * This will receive the 'permissions' array with menu_id and role_id
     * Returns 'Updated' message in JSON format
     */
    public function savePermission(Request $request)
    {
        try {
            $savePermission = $this->permissionService->savePermission($request->permissions);
            return response()->json(['success' => 'Updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
