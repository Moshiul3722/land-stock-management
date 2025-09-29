<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfSubmoduleRequest;
use App\Http\Requests\UpdateCnfSubmoduleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\CnfSubmoduleRepository;
use Illuminate\Http\Request;
use Flash;

class CnfSubmoduleController extends AppBaseController
{
    /** @var CnfSubmoduleRepository $cnfSubmoduleRepository*/
    private $cnfSubmoduleRepository;

    public function __construct(CnfSubmoduleRepository $cnfSubmoduleRepo)
    {
        $this->cnfSubmoduleRepository = $cnfSubmoduleRepo;
    }

    /**
     * Display a listing of the CnfSubmodule.
     */
    public function index(Request $request)
    {
        $cnfSubmodules = $this->cnfSubmoduleRepository->paginate(10);

        return view('cnf_submodules.index')
            ->with('cnfSubmodules', $cnfSubmodules);
    }

    /**
     * Show the form for creating a new CnfSubmodule.
     */
    public function create()
    {
        return view('cnf_submodules.create');
    }

    /**
     * Store a newly created CnfSubmodule in storage.
     */
    public function store(CreateCnfSubmoduleRequest $request)
    {
        $input = $request->all();

        $cnfSubmodule = $this->cnfSubmoduleRepository->create($input);

        Flash::success('Cnf Submodule saved successfully.');

        return redirect(route('cnfSubmodules.index'));
    }

    /**
     * Display the specified CnfSubmodule.
     */
    public function show($id)
    {
        $cnfSubmodule = $this->cnfSubmoduleRepository->find($id);

        if (empty($cnfSubmodule)) {
            Flash::error('Cnf Submodule not found');

            return redirect(route('cnfSubmodules.index'));
        }

        return view('cnf_submodules.show')->with('cnfSubmodule', $cnfSubmodule);
    }

    /**
     * Show the form for editing the specified CnfSubmodule.
     */
    public function edit($id)
    {
        $cnfSubmodule = $this->cnfSubmoduleRepository->find($id);

        if (empty($cnfSubmodule)) {
            Flash::error('Cnf Submodule not found');

            return redirect(route('cnfSubmodules.index'));
        }

        return view('cnf_submodules.edit')->with('cnfSubmodule', $cnfSubmodule);
    }

    /**
     * Update the specified CnfSubmodule in storage.
     */
    public function update($id, UpdateCnfSubmoduleRequest $request)
    {
        $cnfSubmodule = $this->cnfSubmoduleRepository->find($id);

        if (empty($cnfSubmodule)) {
            Flash::error('Cnf Submodule not found');

            return redirect(route('cnfSubmodules.index'));
        }

        $cnfSubmodule = $this->cnfSubmoduleRepository->update($request->all(), $id);

        Flash::success('Cnf Submodule updated successfully.');

        return redirect(route('cnfSubmodules.index'));
    }

    /**
     * Remove the specified CnfSubmodule from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cnfSubmodule = $this->cnfSubmoduleRepository->find($id);

        if (empty($cnfSubmodule)) {
            Flash::error('Cnf Submodule not found');

            return redirect(route('cnfSubmodules.index'));
        }

        $this->cnfSubmoduleRepository->delete($id);

        Flash::success('Cnf Submodule deleted successfully.');

        return redirect(route('cnfSubmodules.index'));
    }
}
