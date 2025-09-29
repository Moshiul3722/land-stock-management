<?php

namespace App\Http\Controllers;

use Log;
use Flash;
use Response;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use App\Services\EmailService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateCnfRoleRequest;
use App\Http\Controllers\ServiceControllers\RoleServiceController;
use App\Http\Controllers\ServiceControllers\UserServiceController;

class UserController extends AppBaseController
{
    /** @var RoleServiceController $roleServiceController*/
    private $roleServiceController;
    /** @var UserServiceController $userServiceController*/
    private $userServiceController;

    public function __construct(RoleServiceController $roleServiceController, UserServiceController $userServiceController)
    {
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
        return view('users.create');
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
        ]);

        try {
            $user = $this->userServiceController->create($request);

            if (!$user) {
                throw new \Exception("Failed to create user.");
            }
            
            $emailService = new EmailService;
            $emailService -> sendWelcomeEmail(
                $request->input('email'),
                $request->input('name'),
                $request->input('email'),
                $request->input('password')
            );

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

            return view('users.edit')->with('user', $user->getOriginalContent());
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
