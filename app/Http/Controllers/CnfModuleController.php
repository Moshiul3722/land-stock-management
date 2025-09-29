<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfModuleRequest;
use App\Http\Requests\UpdateCnfModuleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\CnfModuleRepository;
use Illuminate\Http\Request;
use Flash;

class CnfModuleController extends AppBaseController
{
    /** @var CnfModuleRepository $cnfModuleRepository*/
    private $cnfModuleRepository;

    public function __construct(CnfModuleRepository $cnfModuleRepo)
    {
        $this->cnfModuleRepository = $cnfModuleRepo;
    }

    /**
     * Display a listing of the CnfModule.
     */
    public function index(Request $request)
    {
        $cnfModules = $this->cnfModuleRepository->paginate(10);

        return view('cnf_modules.index')
            ->with('cnfModules', $cnfModules);
    }

    /**
     * Show the form for creating a new CnfModule.
     */
    public function create()
    {
        return view('cnf_modules.create');
    }

    /**
     * Store a newly created CnfModule in storage.
     */
    public function store(CreateCnfModuleRequest $request)
    {
        $input = $request->all();

        $cnfModule = $this->cnfModuleRepository->create($input);

        Flash::success('Cnf Module saved successfully.');

        return redirect(route('cnfModules.index'));
    }

    /**
     * Display the specified CnfModule.
     */
    public function show($id)
    {
        $cnfModule = $this->cnfModuleRepository->find($id);

        if (empty($cnfModule)) {
            Flash::error('Cnf Module not found');

            return redirect(route('cnfModules.index'));
        }

        return view('cnf_modules.show')->with('cnfModule', $cnfModule);
    }

    /**
     * Show the form for editing the specified CnfModule.
     */
    public function edit($id)
    {
        $cnfModule = $this->cnfModuleRepository->find($id);

        if (empty($cnfModule)) {
            Flash::error('Cnf Module not found');

            return redirect(route('cnfModules.index'));
        }

        return view('cnf_modules.edit')->with('cnfModule', $cnfModule);
    }

    /**
     * Update the specified CnfModule in storage.
     */
    public function update($id, UpdateCnfModuleRequest $request)
    {
        $cnfModule = $this->cnfModuleRepository->find($id);

        if (empty($cnfModule)) {
            Flash::error('Cnf Module not found');

            return redirect(route('cnfModules.index'));
        }

        $cnfModule = $this->cnfModuleRepository->update($request->all(), $id);

        Flash::success('Cnf Module updated successfully.');

        return redirect(route('cnfModules.index'));
    }

    /**
     * Remove the specified CnfModule from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cnfModule = $this->cnfModuleRepository->find($id);

        if (empty($cnfModule)) {
            Flash::error('Cnf Module not found');

            return redirect(route('cnfModules.index'));
        }

        $this->cnfModuleRepository->delete($id);

        Flash::success('Cnf Module deleted successfully.');

        return redirect(route('cnfModules.index'));
    }
}
