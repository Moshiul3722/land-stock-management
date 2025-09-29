<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfPermissionRequest;
use App\Http\Requests\UpdateCnfPermissionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\CnfPermissionRepository;
use Illuminate\Http\Request;
use Flash;

class CnfPermissionController extends AppBaseController
{
    /** @var CnfPermissionRepository $cnfPermissionRepository*/
    private $cnfPermissionRepository;

    public function __construct(CnfPermissionRepository $cnfPermissionRepo)
    {
        $this->cnfPermissionRepository = $cnfPermissionRepo;
    }

    /**
     * Display a listing of the CnfPermission.
     */
    public function index(Request $request)
    {
        $cnfPermissions = $this->cnfPermissionRepository->paginate(10);

        return view('cnf_permissions.index')
            ->with('cnfPermissions', $cnfPermissions);
    }

    /**
     * Show the form for creating a new CnfPermission.
     */
    public function create()
    {
        return view('cnf_permissions.create');
    }

    /**
     * Store a newly created CnfPermission in storage.
     */
    public function store(CreateCnfPermissionRequest $request)
    {
        $input = $request->all();

        $cnfPermission = $this->cnfPermissionRepository->create($input);

        Flash::success('Cnf Permission saved successfully.');

        return redirect(route('cnfPermissions.index'));
    }

    /**
     * Display the specified CnfPermission.
     */
    public function show($id)
    {
        $cnfPermission = $this->cnfPermissionRepository->find($id);

        if (empty($cnfPermission)) {
            Flash::error('Cnf Permission not found');

            return redirect(route('cnfPermissions.index'));
        }

        return view('cnf_permissions.show')->with('cnfPermission', $cnfPermission);
    }

    /**
     * Show the form for editing the specified CnfPermission.
     */
    public function edit($id)
    {
        $cnfPermission = $this->cnfPermissionRepository->find($id);

        if (empty($cnfPermission)) {
            Flash::error('Cnf Permission not found');

            return redirect(route('cnfPermissions.index'));
        }

        return view('cnf_permissions.edit')->with('cnfPermission', $cnfPermission);
    }

    /**
     * Update the specified CnfPermission in storage.
     */
    public function update($id, UpdateCnfPermissionRequest $request)
    {
        $cnfPermission = $this->cnfPermissionRepository->find($id);

        if (empty($cnfPermission)) {
            Flash::error('Cnf Permission not found');

            return redirect(route('cnfPermissions.index'));
        }

        $cnfPermission = $this->cnfPermissionRepository->update($request->all(), $id);

        Flash::success('Cnf Permission updated successfully.');

        return redirect(route('cnfPermissions.index'));
    }

    /**
     * Remove the specified CnfPermission from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cnfPermission = $this->cnfPermissionRepository->find($id);

        if (empty($cnfPermission)) {
            Flash::error('Cnf Permission not found');

            return redirect(route('cnfPermissions.index'));
        }

        $this->cnfPermissionRepository->delete($id);

        Flash::success('Cnf Permission deleted successfully.');

        return redirect(route('cnfPermissions.index'));
    }
}
