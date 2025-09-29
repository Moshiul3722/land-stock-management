<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\StudentsApplicationForm;
use App\Repositories\CnfGenderRepository;
use App\Repositories\ScholarshipCategoryRepository;
use App\Repositories\ScholarshipCategoryAExamRepository;


class StApplicationFormService
{

    protected $scholarshipCategoryRepository;
    protected $cnfGenderRepository;
    protected $scholarshipCategoryAExamRepository;

    public function __construct(
        ScholarshipCategoryRepository $scholarshipCategoryRepository,
        CnfGenderRepository $cnfGenderRepository,
        ScholarshipCategoryAExamRepository $scholarshipCategoryAExamRepository
    ) {
        $this->scholarshipCategoryRepository = $scholarshipCategoryRepository;
        $this->cnfGenderRepository = $cnfGenderRepository;
        $this->scholarshipCategoryAExamRepository = $scholarshipCategoryAExamRepository;
    }


    public function StApplicationFormView()
    {
        // Check if WelcomeEmail module is active
        $studentsApplicationForm = StudentsApplicationForm::with(['st_category','st_sub_category','st_class','st_shift', 'st_gender','st_religion','st_exam','st_institute','st_bankName','st_bankBranch','get_upazila']);

        return $studentsApplicationForm;
    }

    public function getScholarshipCategories()
    {
        return $this->scholarshipCategoryRepository->all();
    }

    public function getGenders()
    {
        return $this->cnfGenderRepository->all();
    }

    public function getAllExamNames()
    {
        return $this->scholarshipCategoryAExamRepository->all();
    }

    
}
