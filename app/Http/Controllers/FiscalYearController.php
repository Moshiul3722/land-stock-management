<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFiscalYearRequest;
use App\Http\Requests\UpdateFiscalYearRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\FiscalYearRepository;
use Illuminate\Http\Request;
use Flash;

class FiscalYearController extends AppBaseController
{
    /** @var FiscalYearRepository $fiscalYearRepository*/
    private $fiscalYearRepository;

    public function __construct(FiscalYearRepository $fiscalYearRepo)
    {
        $this->fiscalYearRepository = $fiscalYearRepo;
    }

    /**
     * Display a listing of the FiscalYear.
     */
    public function index(Request $request)
    {
        $fiscalYears = $this->fiscalYearRepository->paginate(10);

        return view('fiscal_years.index')
            ->with('fiscalYears', $fiscalYears);
    }

    /**
     * Show the form for creating a new FiscalYear.
     */
    public function create()
    {
        return view('fiscal_years.create');
    }

    /**
     * Store a newly created FiscalYear in storage.
     */
    public function store(CreateFiscalYearRequest $request)
    {
        $input = $request->all();

        $fiscalYear = $this->fiscalYearRepository->create($input);

        Flash::success('Fiscal Year saved successfully.');

        return redirect(route('fiscalYears.index'));
    }

    /**
     * Display the specified FiscalYear.
     */
    public function show($id)
    {
        $fiscalYear = $this->fiscalYearRepository->find($id);

        if (empty($fiscalYear)) {
            Flash::error('Fiscal Year not found');

            return redirect(route('fiscalYears.index'));
        }

        return view('fiscal_years.show')->with('fiscalYear', $fiscalYear);
    }

    /**
     * Show the form for editing the specified FiscalYear.
     */
    public function edit($id)
    {
        $fiscalYear = $this->fiscalYearRepository->find($id);

        if (empty($fiscalYear)) {
            Flash::error('Fiscal Year not found');

            return redirect(route('fiscalYears.index'));
        }

        return view('fiscal_years.edit')->with('fiscalYear', $fiscalYear);
    }

    /**
     * Update the specified FiscalYear in storage.
     */
    public function update($id, UpdateFiscalYearRequest $request)
    {
        $fiscalYear = $this->fiscalYearRepository->find($id);

        if (empty($fiscalYear)) {
            Flash::error('Fiscal Year not found');

            return redirect(route('fiscalYears.index'));
        }

        $fiscalYear = $this->fiscalYearRepository->update($request->all(), $id);

        Flash::success('Fiscal Year updated successfully.');

        return redirect(route('fiscalYears.index'));
    }

    /**
     * Remove the specified FiscalYear from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $fiscalYear = $this->fiscalYearRepository->find($id);

        if (empty($fiscalYear)) {
            Flash::error('Fiscal Year not found');

            return redirect(route('fiscalYears.index'));
        }

        $this->fiscalYearRepository->delete($id);

        Flash::success('Fiscal Year deleted successfully.');

        return redirect(route('fiscalYears.index'));
    }
}
