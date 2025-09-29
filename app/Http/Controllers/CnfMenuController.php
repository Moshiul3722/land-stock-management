<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCnfMenuRequest;
use App\Http\Requests\UpdateCnfMenuRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\CnfMenuRepository;
use Illuminate\Http\Request;
use Flash;

class CnfMenuController extends AppBaseController
{
    /** @var CnfMenuRepository $cnfMenuRepository*/
    private $cnfMenuRepository;

    public function __construct(CnfMenuRepository $cnfMenuRepo)
    {
        $this->cnfMenuRepository = $cnfMenuRepo;
    }

    /**
     * Display a listing of the CnfMenu.
     */
    public function index(Request $request)
    {
        $cnfMenus = $this->cnfMenuRepository->paginate(10);

        return view('cnf_menus.index')
            ->with('cnfMenus', $cnfMenus);
    }

    /**
     * Show the form for creating a new CnfMenu.
     */
    public function create()
    {
        return view('cnf_menus.create');
    }

    /**
     * Store a newly created CnfMenu in storage.
     */
    public function store(CreateCnfMenuRequest $request)
    {
        $input = $request->all();

        $cnfMenu = $this->cnfMenuRepository->create($input);

        Flash::success('Cnf Menu saved successfully.');

        return redirect(route('cnfMenus.index'));
    }

    /**
     * Display the specified CnfMenu.
     */
    public function show($id)
    {
        $cnfMenu = $this->cnfMenuRepository->find($id);

        if (empty($cnfMenu)) {
            Flash::error('Cnf Menu not found');

            return redirect(route('cnfMenus.index'));
        }

        return view('cnf_menus.show')->with('cnfMenu', $cnfMenu);
    }

    /**
     * Show the form for editing the specified CnfMenu.
     */
    public function edit($id)
    {
        $cnfMenu = $this->cnfMenuRepository->find($id);

        if (empty($cnfMenu)) {
            Flash::error('Cnf Menu not found');

            return redirect(route('cnfMenus.index'));
        }

        return view('cnf_menus.edit')->with('cnfMenu', $cnfMenu);
    }

    /**
     * Update the specified CnfMenu in storage.
     */
    public function update($id, UpdateCnfMenuRequest $request)
    {
        $cnfMenu = $this->cnfMenuRepository->find($id);

        if (empty($cnfMenu)) {
            Flash::error('Cnf Menu not found');

            return redirect(route('cnfMenus.index'));
        }

        $cnfMenu = $this->cnfMenuRepository->update($request->all(), $id);

        Flash::success('Cnf Menu updated successfully.');

        return redirect(route('cnfMenus.index'));
    }

    /**
     * Remove the specified CnfMenu from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $cnfMenu = $this->cnfMenuRepository->find($id);

        if (empty($cnfMenu)) {
            Flash::error('Cnf Menu not found');

            return redirect(route('cnfMenus.index'));
        }

        $this->cnfMenuRepository->delete($id);

        Flash::success('Cnf Menu deleted successfully.');

        return redirect(route('cnfMenus.index'));
    }
}
