<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateYearRequest;
use App\Http\Requests\UpdateYearRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\YearRepository;
use Illuminate\Http\Request;
use Flash;

class YearController extends AppBaseController
{
    /** @var YearRepository $yearRepository*/
    private $yearRepository;

    public function __construct(YearRepository $yearRepo)
    {
        $this->yearRepository = $yearRepo;
    }

    /**
     * Display a listing of the Year.
     */
    public function index(Request $request)
    {
        $years = $this->yearRepository->paginate(10);

        return view('years.index')
            ->with('years', $years);
    }

    /**
     * Show the form for creating a new Year.
     */
    public function create()
    {
        return view('years.create');
    }

    /**
     * Store a newly created Year in storage.
     */
    public function store(CreateYearRequest $request)
    {
        $input = $request->all();

        $year = $this->yearRepository->create($input);

        Flash::success('Year saved successfully.');

        return redirect(route('years.index'));
    }

    /**
     * Display the specified Year.
     */
    public function show($id)
    {
        $year = $this->yearRepository->find($id);

        if (empty($year)) {
            Flash::error('Year not found');

            return redirect(route('years.index'));
        }

        return view('years.show')->with('year', $year);
    }

    /**
     * Show the form for editing the specified Year.
     */
    public function edit($id)
    {
        $year = $this->yearRepository->find($id);

        if (empty($year)) {
            Flash::error('Year not found');

            return redirect(route('years.index'));
        }

        return view('years.edit')->with('year', $year);
    }

    /**
     * Update the specified Year in storage.
     */
    public function update($id, UpdateYearRequest $request)
    {
        $year = $this->yearRepository->find($id);

        if (empty($year)) {
            Flash::error('Year not found');

            return redirect(route('years.index'));
        }

        $year = $this->yearRepository->update($request->all(), $id);

        Flash::success('Year updated successfully.');

        return redirect(route('years.index'));
    }

    /**
     * Remove the specified Year from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $year = $this->yearRepository->find($id);

        if (empty($year)) {
            Flash::error('Year not found');

            return redirect(route('years.index'));
        }

        $this->yearRepository->delete($id);

        Flash::success('Year deleted successfully.');

        return redirect(route('years.index'));
    }
}
