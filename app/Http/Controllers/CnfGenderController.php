<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfGenderRequest;
use App\Http\Requests\UpdateCnfGenderRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\CnfGenderRepository;
use Illuminate\Http\Request;
use Flash;

class CnfGenderController extends AppBaseController
{
    /** @var CnfGenderRepository $cnfGenderRepository*/
    private $cnfGenderRepository;

    public function __construct(CnfGenderRepository $cnfGenderRepo)
    {
        $this->cnfGenderRepository = $cnfGenderRepo;
    }

    /**
     * Display a listing of the CnfGender.
     */
    public function index(Request $request)
    {
        $cnfGenders = $this->cnfGenderRepository->paginate(10);

        return view('cnf_genders.index')
            ->with('cnfGenders', $cnfGenders);
    }

    /**
     * Show the form for creating a new CnfGender.
     */
    public function create()
    {
        return view('cnf_genders.create');
    }

    /**
     * Store a newly created CnfGender in storage.
     */
    public function store(CreateCnfGenderRequest $request)
    {
        $input = $request->all();

        $cnfGender = $this->cnfGenderRepository->create($input);

        Flash::success('Cnf Gender saved successfully.');

        return redirect(route('cnfGenders.index'));
    }

    /**
     * Display the specified CnfGender.
     */
    public function show($id)
    {
        $cnfGender = $this->cnfGenderRepository->find($id);

        if (empty($cnfGender)) {
            Flash::error('Cnf Gender not found');

            return redirect(route('cnfGenders.index'));
        }

        return view('cnf_genders.show')->with('cnfGender', $cnfGender);
    }

    /**
     * Show the form for editing the specified CnfGender.
     */
    public function edit($id)
    {
        $cnfGender = $this->cnfGenderRepository->find($id);

        if (empty($cnfGender)) {
            Flash::error('Cnf Gender not found');

            return redirect(route('cnfGenders.index'));
        }

        return view('cnf_genders.edit')->with('cnfGender', $cnfGender);
    }

    /**
     * Update the specified CnfGender in storage.
     */
    public function update($id, UpdateCnfGenderRequest $request)
    {
        $cnfGender = $this->cnfGenderRepository->find($id);

        if (empty($cnfGender)) {
            Flash::error('Cnf Gender not found');

            return redirect(route('cnfGenders.index'));
        }

        $cnfGender = $this->cnfGenderRepository->update($request->all(), $id);

        Flash::success('Cnf Gender updated successfully.');

        return redirect(route('cnfGenders.index'));
    }

    /**
     * Remove the specified CnfGender from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cnfGender = $this->cnfGenderRepository->find($id);

        if (empty($cnfGender)) {
            Flash::error('Cnf Gender not found');

            return redirect(route('cnfGenders.index'));
        }

        $this->cnfGenderRepository->delete($id);

        Flash::success('Cnf Gender deleted successfully.');

        return redirect(route('cnfGenders.index'));
    }
}
