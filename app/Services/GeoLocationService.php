<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Repositories\DistrictsRepository;
use App\Repositories\DivisionsRepository;
use App\Repositories\MouzaRepository;
use App\Repositories\UnionsRepository;
use App\Repositories\UpazilasRepository;
use App\Repositories\VillagesRepository;

class GeoLocationService
{
    /** @var DivisionsRepository $divisionsRepository*/
    private $divisionsRepository;
    /** @var DistrictsRepository $districtsRepository*/
    private $districtsRepository;
    /** @var UpazilasRepository $upazilasRepository*/
    private $upazilasRepository;
    /** @var UnionsRepository $unionsRepository*/
    private $unionsRepository;
    /** @var MouzaRepository $mouzaRepository*/
    private $mouzaRepository;
    /** @var VillagesRepository $villagesRepository*/
    private $villagesRepository;

    public function __construct(
        DivisionsRepository $divisionsRepository,
        DistrictsRepository $districtsRepository,
        UpazilasRepository $upazilasRepository,
        UnionsRepository $unionsRepository,
        MouzaRepository $mouzaRepository,
        VillagesRepository $villagesRepository
    ) {
        $this->divisionsRepository = $divisionsRepository;
        $this->districtsRepository = $districtsRepository;
        $this->upazilasRepository = $upazilasRepository;
        $this->unionsRepository = $unionsRepository;
        $this->mouzaRepository = $mouzaRepository;
        $this->villagesRepository = $villagesRepository;
    }

    public function getAllDivisions()
    {
        try {
            $divisions = $this->divisionsRepository->all();
            return response()->json(
                $divisions
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getDistrictsByDivisionId($divisionId)
    {
        $searchCriteria = [];
        $searchCriteria['division_id'] = $divisionId;
        try {
            $districts = $this->districtsRepository->all($searchCriteria);
            return response()->json(
                $districts, 200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getUpazillasByDistrictId($districtId)
    {
        $searchCriteria = [];
        $searchCriteria['district_id'] = $districtId;
        try {
            $upazilas = $this->upazilasRepository->all($searchCriteria);
            return response()->json(
                $upazilas
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getUnionsByUpazilaId($upazilaId)
    {
        $searchCriteria = [];
        $searchCriteria['upazila_id'] = $upazilaId;
        try {
            $unions = $this->unionsRepository->all($searchCriteria);
            return response()->json(
                $unions
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getMouzasByUnionId($unionId)
    {
        $searchCriteria = [];
        $searchCriteria['union_id'] = $unionId;
        try {
            $mouzas = $this->mouzaRepository->all($searchCriteria);
            return response()->json(
                $mouzas
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getVillagesByMouzaId($mouzaId)
    {
        $searchCriteria = [];
        $searchCriteria['mouza_id'] = $mouzaId;
        try {
            $villages = $this->villagesRepository->all($searchCriteria);
            return response()->json(
                $villages
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAllDistricts()
    {
        try {
            $districts = $this->districtsRepository->all();
            return response()->json(
                $districts, 200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
