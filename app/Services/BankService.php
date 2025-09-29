<?php

namespace App\Services;

use App\Repositories\CnfBankDistrictRepository;
use App\Repositories\CnfBankRepository;
use App\Repositories\CnfBankBranchRepository;
use App\Repositories\CnfBanksDistrictsRepository;
use Illuminate\Http\Request;

class BankService
{
    /** @var CnfBankDistrictRepository $cnfBankDistrictRepository*/
    private $cnfBankDistrictRepository;
    /** @var CnfBankRepository $cnfBankRepository*/
    private $cnfBankRepository;
    /** @var CnfBankBranchRepository $cnfBankBranchRepository*/
    private $cnfBankBranchRepository;
    /** @var CnfBanksDistrictsRepository $cnfBanksDistrictsRepository*/
    private $cnfBanksDistrictsRepository;

    public function __construct(
        CnfBankDistrictRepository $cnfBankDistrictRepo,
        CnfBankRepository $cnfBankRepo,
        CnfBankBranchRepository $cnfBankBranchRepo,
        CnfBanksDistrictsRepository $cnfBanksDistrictsRepo
    ) {
        $this->cnfBankDistrictRepository = $cnfBankDistrictRepo;
        $this->cnfBankRepository = $cnfBankRepo;
        $this->cnfBankBranchRepository = $cnfBankBranchRepo;
        $this->cnfBanksDistrictsRepository = $cnfBanksDistrictsRepo;
    }

    public function getBankDistricts()
    {
        $cnfBankDistricts = $this->cnfBankDistrictRepository->all();
        return response()->json($cnfBankDistricts);
    }

    public function getBanks()
    {
        $cnfBanks = $this->cnfBankRepository->all();
        return response()->json($cnfBanks);
    }

    public function getBanksByDistrict($bankDistrictId)
    {
        // Define the base search criteria
        $searchCriteria = [
            'bank_dist_id' => $bankDistrictId
        ];
        $cnfBanks = $this->cnfBanksDistrictsRepository->all($searchCriteria);
        return response()->json($cnfBanks);
    }

    public function getDistrictsByBank($bankId)
    {
        // Define the base search criteria
        $searchCriteria = [
            'bank_id' => $bankId
        ];
        $districts = $this->cnfBanksDistrictsRepository->all($searchCriteria);
        return response()->json($districts);
    }

    //Bank Routing from Branch

    public function getRoutingNumberbyBranchId($searchCriteria)
    {

        $data = $this->cnfBankBranchRepository->all($searchCriteria);
        $data = $data->first();
        $routing_no = $data->routing_no;

        return $routing_no;
    }

    public function getBankBranchesByDistrictAndBank($bankId, $bankDistrictId, $isActive = null)
    {

        // Define the base search criteria
        $searchCriteria = [
            'bank_id' => $bankId,
            'bank_dist_id' => $bankDistrictId,
        ];

        // Add the 'is_active' condition if $isActive is not null
        if ($isActive !== null) {
            $searchCriteria['is_active'] = $isActive;
        }
        // Retrieve bank branches matching the search criteria
        $cnfBankBranches = $this->cnfBankBranchRepository->all($searchCriteria);
        return response()->json($cnfBankBranches);
    }

    public function create(Request $request)
    {
        try {
            $input = $request->all();
            $input['is_active'] = 1;
            $bank = $this->cnfBankRepository->create($input);
            return response()->json($bank);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a New Entry in the cnf_bank_branch Table
     * Create a New Bank-Branch
     * Method - POST
     * Route Name - bankservice.storebranch
     */
    public function createBankBranch(Request $request)
    {
        try {
            $input = $request->all();
            $input['is_active'] = 1;
            $bankBranch = $this->cnfBankBranchRepository->create($input);
            return response()->json($bankBranch);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Find a specific branch by Id
     * Method - POST
     */
    public function findBranchById($id)
    {
        try {
            $bankBranch = $this->cnfBankBranchRepository->find($id);
            $bankBranch->load('bank', 'bankDist');
            return response()->json($bankBranch);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
