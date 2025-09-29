<?php

namespace App\Services;

use App\Models\ImsInstitute;
use Illuminate\Support\Facades\DB;
use App\Repositories\CnfDistrictRepository;
use App\Repositories\CnfUpazilaRepository;
use Exception;
use App\Repositories\ImsInsCategoryRepository;
use App\Repositories\ImsInsTypeRepository;
use App\Repositories\ImsInsLevelRepository;
use App\Repositories\ImsInsManagementRepository;
use App\Repositories\ImsInstituteRepository;

class InstituteManagementService
{

    protected $imsInsCategoryRepository;
    protected $imsInsTypeRepository;
    protected $imsInsLevelRepositroy;
    protected $imsInsManagementRepositroy;
    protected $cnfDistrictRepository;
    protected $cnfUpazilaRepository;
    protected  $imsInstituteRepository;

    public function __construct(ImsInsCategoryRepository $imsInsCategoryRepository, ImsInsTypeRepository $imsInsTypeRepository, ImsInsLevelRepository $imsInsLevelRepositroy, ImsInsManagementRepository $imsInsManagementRepositroy, ImsInstituteRepository $imsInstituteRepository)
    {
        $this->imsInsCategoryRepository = $imsInsCategoryRepository;
        $this->imsInsTypeRepository = $imsInsTypeRepository;
        $this->imsInsLevelRepositroy = $imsInsLevelRepositroy;
        $this->imsInsManagementRepositroy = $imsInsManagementRepositroy;
        $this->imsInstituteRepository = $imsInstituteRepository;
    }



    public function getCategories()
    {
        try {
            $categories = $this->imsInsCategoryRepository->all();
            return response()->json(
                $categories
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLevels()
    {
        try {
            $categories = $this->imsInsLevelRepositroy->all();
            return response()->json(
                $categories
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getManagements()
    {
        try {
            $categories = $this->imsInsManagementRepositroy->all();
            return response()->json(
                $categories
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTypes()
    {
        try {
            $categories = $this->imsInsTypeRepository->all();
            return response()->json(
                $categories
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getInstitutes($searchCriteria)
    {

        try {
            $institutes = $this->imsInstituteRepository->all($searchCriteria);
            // Eager load relationships
            $institutes->load('zone', 'level', 'district', 'category', 'type', 'division', 'management', 'upazila');
            return response()->json(
                $institutes
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getInstituteById($instituteId)
    {
        try {
            $institute = $this->imsInstituteRepository->find($instituteId);
            if (!$institute) {
                return response()->json(['error' => 'Institute not found'], 404);
            }
            $institute->load('zone', 'level', 'district', 'category', 'type', 'division', 'management', 'upazila');


            return response()->json(
                $institute
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
