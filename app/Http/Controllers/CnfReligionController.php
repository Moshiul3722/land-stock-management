<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfReligionRequest;
use App\Http\Requests\UpdateCnfReligionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\CnfReligionRepository;
use Illuminate\Http\Request;
use Flash;

class CnfReligionController extends AppBaseController
{
    /** @var CnfReligionRepository $cnfReligionRepository*/
    private $cnfReligionRepository;

    public function __construct(CnfReligionRepository $cnfReligionRepo)
    {
        $this->cnfReligionRepository = $cnfReligionRepo;
    }

    /**
     * Display a listing of the CnfReligion.
     */
    public function index(Request $request)
    {
        $cnfReligions = $this->cnfReligionRepository->paginate(10);

        return view('cnf_religions.index')
            ->with('cnfReligions', $cnfReligions);
    }

    /**
     * Show the form for creating a new CnfReligion.
     */
    public function create()
    {
        return view('cnf_religions.create');
    }

    /**
     * Store a newly created CnfReligion in storage.
     */
    public function store(CreateCnfReligionRequest $request)
    {
        $input = $request->all();

        $cnfReligion = $this->cnfReligionRepository->create($input);

        Flash::success('Cnf Religion saved successfully.');

        return redirect(route('cnfReligions.index'));
    }

    /**
     * Display the specified CnfReligion.
     */
    public function show($id)
    {
        $cnfReligion = $this->cnfReligionRepository->find($id);

        if (empty($cnfReligion)) {
            Flash::error('Cnf Religion not found');

            return redirect(route('cnfReligions.index'));
        }

        return view('cnf_religions.show')->with('cnfReligion', $cnfReligion);
    }

    /**
     * Show the form for editing the specified CnfReligion.
     */
    public function edit($id)
    {
        $cnfReligion = $this->cnfReligionRepository->find($id);

        if (empty($cnfReligion)) {
            Flash::error('Cnf Religion not found');

            return redirect(route('cnfReligions.index'));
        }

        return view('cnf_religions.edit')->with('cnfReligion', $cnfReligion);
    }

    /**
     * Update the specified CnfReligion in storage.
     */
    public function update($id, UpdateCnfReligionRequest $request)
    {
        $cnfReligion = $this->cnfReligionRepository->find($id);

        if (empty($cnfReligion)) {
            Flash::error('Cnf Religion not found');

            return redirect(route('cnfReligions.index'));
        }

        $cnfReligion = $this->cnfReligionRepository->update($request->all(), $id);

        Flash::success('Cnf Religion updated successfully.');

        return redirect(route('cnfReligions.index'));
    }

    /**
     * Remove the specified CnfReligion from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cnfReligion = $this->cnfReligionRepository->find($id);

        if (empty($cnfReligion)) {
            Flash::error('Cnf Religion not found');

            return redirect(route('cnfReligions.index'));
        }

        $this->cnfReligionRepository->delete($id);

        Flash::success('Cnf Religion deleted successfully.');

        return redirect(route('cnfReligions.index'));
    }

    public function getAllReligions(Request $request)
    {
        $religions = $this->cnfReligionRepository->all();
        return $religions;
    }
}
