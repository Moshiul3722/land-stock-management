<?php

namespace App\Services;

use App\Models\Beneficiary;
use App\Repositories\BeneficiaryRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateBeneficiaryRequest;
use App\Http\Requests\UpdateBeneficiaryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ScholarshipCategoryAExamRepository;
use App\Repositories\EducationBoardRepository;
use App\Repositories\YearRepository;
use App\Repositories\GazetteRepository;
use App\Repositories\GazetteFileRepository;
use App\Repositories\CnfGenderRepository;
use App\Repositories\GroupRepository;
use App\Repositories\CnfBankRepository;
use App\Repositories\CnfBankAccountTypesRepository;
use Illuminate\Http\Request;
use Flash;
use Exception;

class BeneficiaryService
{
    private $beneficiaryRepository;
    protected $scholarshipCategoryAExamRepository;
    protected $educationBoardRepository;
    protected $yearRepository;
    protected $gazetteRepository;
    protected $gazetteFileRepositroy;
    protected $cnfGenderRepository;
    protected $groupRepository;
    protected $cnfBankRepository;
    protected $cnfBankAccountTypesRepository;

    public function __construct(CnfBankAccountTypesRepository $cnfBankAccountTypesRepository, CnfBankRepository $cnfBankRepository, CnfGenderRepository $cnfGenderRepository, GroupRepository $groupRepository, GazetteRepository $gazetteRepository, GazetteFileRepository $gazetteFileRepositroy, BeneficiaryRepository $beneficiaryRepo, ScholarshipCategoryAExamRepository $scholarshipCategoryAExamRepository, EducationBoardRepository $educationBoardRepository, YearRepository $yearRepository)
    {
        $this->beneficiaryRepository = $beneficiaryRepo;
        $this->scholarshipCategoryAExamRepository = $scholarshipCategoryAExamRepository;
        $this->educationBoardRepository = $educationBoardRepository;
        $this->yearRepository = $yearRepository;
        $this->gazetteRepository = $gazetteRepository;
        $this->gazetteFileRepositroy = $gazetteFileRepositroy;
        $this->cnfGenderRepository = $cnfGenderRepository;
        $this->groupRepository = $groupRepository;
        $this->cnfBankRepository = $cnfBankRepository;
        $this->cnfBankAccountTypesRepository = $cnfBankAccountTypesRepository;
    }

    public function search($request)
    {
        $path = Storage::disk($disk)->put($path, $file);
        $fileRecord = File::create([
            'file_name' => $file->getClientOriginalName(),
            'module_name' => $module,
            'path' => $path,
            'disk' => $disk,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id,
        ]);
        return $fileRecord;
    }
}
