<?php

namespace App\Services;

use App\Repositories\CnfRoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /** @var CnfRoleRepository $cnfRoleRepository*/
    private $cnfRoleRepository;
    /** @var UserRepository $userRepository*/
    private $userRepository;

    public function __construct(CnfRoleRepository $cnfRoleRepo, UserRepository $userRepo)
    {
        $this->cnfRoleRepository = $cnfRoleRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Get List of All Roles
     */
    public function getAllUsers()
    {
        $rolesList = $this->cnfRoleRepository->allWithUser();
        return response()->json($rolesList);
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
     * Get All User Types
     */
    public function getAllUserTypes()
    {
        try {
            $userTypesList = $this->userRepository->getAllUserTypes();
            return response()->json($userTypesList);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create New User (Storing new user to Users Table)
     */
    public function create(Request $request)
    {
        try {
            $input = $request->all();
            $input['password'] = Hash::make($input['password']);
            $user = $this->userRepository->create($input);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Find a User with Id
     */
    public function find($userId)
    {
        try {
            $user = $this->userRepository->find($userId);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update User Information
     */
    public function update($user, $userId)
    {
        try {
            $user = $this->userRepository->update($user, $userId);
            return $user;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the total number of users.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->userRepository->count();
    }
}
