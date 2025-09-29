<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfRoleRequest;
use App\Http\Requests\UpdateCnfRoleRequest;
use App\Repositories\CnfRoleRepository;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\ServiceControllers\RoleServiceController;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Flash;
use Response;
use Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AuthHelperController;
use Illuminate\Validation\Rule;

class CnfRoleController extends AppBaseController
{
    /** @var CnfRoleRepository $cnfRoleRepository*/
    private $cnfRoleRepository;
    /** @var RoleService $roleService*/
    private $roleService;
    /** @var RoleServiceController $roleServiceController*/
    private $roleServiceController;

    public function __construct(CnfRoleRepository $cnfRoleRepo, RoleService $roleService, RoleServiceController $roleServiceController)
    {
        $this->cnfRoleRepository = $cnfRoleRepo;
        $this->roleService = $roleService;
        $this->roleServiceController = $roleServiceController;
    }

    /**
     * Display a listing of the CnfRole.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        try {
            $listOfRoles = $this->roleServiceController->getAllRoles();
            $listOfRoles = $listOfRoles->getOriginalContent();
            return view('cnf_roles.index')
                ->with('listOfRoles', $listOfRoles);
        } catch (\Exception $e) {
            // Log the error for further investigation if needed
            Log::error('Error occurred while fetching roles: ' . $e->getMessage());

            // Return a flash response back
            return redirect()->back()->with('error', 'An error occurred while fetching roles. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new CnfRole.
     *
     * @return Response
     */
    public function create()
    {
        return view('cnf_roles.create');
    }

    /**
     * Store a newly created CnfRole in storage.
     *
     * @param CreateCnfRoleRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'role_name_en' => 'required|unique:cnf_role,role_name_en|max:255',
            'role_name_bn' => 'required|unique:cnf_role,role_name_bn|max:255',
        ]);
        try {
            $input = $request->all();
            $role = $this->cnfRoleRepository->create($input);
            return response()->json($role);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified CnfRole.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $cnfRole = $this->cnfRoleRepository->find($id);

        if (empty($cnfRole)) {
            Flash::error('Cnf Role not found');

            return redirect(route('cnfRoles.index'));
        }

        return view('cnf_roles.show')->with('cnfRole', $cnfRole);
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
        $cnfRole = $this->cnfRoleRepository->find($id);

        if (empty($cnfRole)) {
            Flash::error('Cnf Role not found');

            return redirect(route('cnfRoles.index'));
        }

        return view('cnf_roles.edit')->with('cnfRole', $cnfRole);
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
            'role_name_en' => [
                'required',
                Rule::unique('cnf_role')->ignore($id),
                'max:255',
            ],
            'role_name_bn' => [
                'required',
                Rule::unique('cnf_role')->ignore($id),
                'max:255',
            ],
        ]);

        $cnfRole = $this->cnfRoleRepository->find($id);

        if (empty($cnfRole)) {
            return response()->json(['error' => trans('flash.no_record_found')], 500);
        }

        try {
            $cnfRole = $this->cnfRoleRepository->update($request->all(), $id);
            return response()->json($cnfRole);
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
    public function destroy($id)
    {
        $cnfRole = $this->cnfRoleRepository->find($id);

        if (empty($cnfRole)) {
            return response()->json(['error' => trans('flash.no_record_found')], 500);
        }

        $this->cnfRoleRepository->delete($id);

        return response()->json(['success' => "deleted"], 200);
    }
}
