<?php

namespace App\Http\Controllers;

use Flash;
use Response;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\ServiceControllers\RoleServiceController;
use App\Http\Controllers\ServiceControllers\UserServiceController;
use Illuminate\Support\Facades\Log;
use App\Models\CnfRole;

class UserController extends AppBaseController
{
    /** @var RoleServiceController $roleServiceController*/
    private $roleServiceController;
    /** @var UserServiceController $userServiceController*/
    private $userServiceController;

    public function __construct(
        RoleServiceController $roleServiceController,
        UserServiceController $userServiceController
    ) {
        $this->roleServiceController = $roleServiceController;
        $this->userServiceController = $userServiceController;
    }


    /**
     * Display a listing of the Users.
     */
    public function index()
    {
        try {
            $listOfRoles = $this->roleServiceController->getAllRoles();
            $listOfRoles = $listOfRoles->getOriginalContent();
            return view('users.index')
                ->with('listOfRoles', $listOfRoles);
        } catch (\Exception $e) {
            // Log the error for further investigation if needed
            Log::error('Error occurred while fetching roles: ' . $e->getMessage());

            // Return a flash response back
            return redirect()->back()->with('error', 'An error occurred while fetching users. Please try again later.');
        }
    }
    /**
     * Show the form for creating a new User.
     */
    public function create()
    {
        // Fetch all departments for the dropdown
        $departments = Department::orderBy('name')->get();
        return view('users.create', compact('departments'));
    }

    /**
     * Store a newly created User in storage.
     * Takes Request $request
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email|max:255',
            'password' => 'required|max:255',
            'role_id' => 'required|exists:cnf_role,id',
            'department_id' => 'required_if:role_id,5|exists:departments,id',
        ]);

        try {
            $user = $this->userServiceController->create($request);

            if (!$user) {
                throw new \Exception("Failed to create user.");
            }

            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified CnfRole.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        try {
            $user = $this->userServiceController->find($id);
            // dd($user->getOriginalContent());
            // If not found return to list with error
            if (empty($user)) {
                Flash::error('User not found');
                return redirect(route('users.index'));
            }

            $roles = CnfRole::all();
            // Fetch all departments for the dropdown
            $departments = Department::orderBy('name')->get();

            return view('users.edit')
                ->with('user', $user->getOriginalContent())
                ->with('roles', $roles)
                ->with('departments', $departments);
        } catch (\Exception $e) {
            Flash::error('User not found');
            return redirect(route('users.index'));
        }
        $user = $this->userServiceController->find($id);
    }

    /**
     * Update the specified CnfRole in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $validator = $request->validate([
            'name' => [
                'required',
                'max:255',
            ],
            'email' => [
                'required',
                Rule::unique('users')->ignore($id),
                'max:255',
            ],
        ]);

        $user = $this->userServiceController->find($id);

        if (empty($user)) {
            return response()->json(['error' => trans('flash.no_record_found')], 500);
        }

        try {
            // Check if password is provided in the request
            if ($request->has('password')) {
                // Hash the password
                $request->merge(['password' => Hash::make($request->password)]);
            }
            $user = $this->userServiceController->update($request->all(), $id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified CnfRole from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    // public function destroy($id)
    // {
    //     $cnfRole = $this->cnfRoleRepository->find($id);

    //     if (empty($cnfRole)) {
    //         return response()->json(['error' => trans('flash.no_record_found')], 500);
    //     }

    //     $this->cnfRoleRepository->delete($id);

    //     return response()->json(['success' => "deleted"], 200);
    // }
}
