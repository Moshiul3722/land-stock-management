<?php

namespace App\Services;

use App\Models\ScholarshipCategoryADetails;
use App\Repositories\EducationBoardRepository;
use App\Repositories\GazetteFileRepository;
use App\Repositories\GazetteRepository;
use App\Repositories\ScholarshipCategoryADetailsRepository;
use App\Repositories\ScholarshipCategoryAExamRepository;
use App\Repositories\YearRepository;
use Illuminate\Support\Facades\Auth;
use Exception;

class CatAScholarshipService
{
    /** @var  ScholarshipCategoryAExamRepository $scholarshipCategoryAExamRepository*/
    private $scholarshipCategoryAExamRepository;
    /** @var  YearRepository $yearRepository*/
    private $yearRepository;
    /** @var  EducationBoardRepository $educationBoardRepository*/
    private $educationBoardRepository;
    /** @var  GazetteFileRepository $gazetteFileRepository*/
    private $gazetteFileRepository;
    /** @var  FileService $fileService*/
    private $fileService;
    /** @var  GazetteRepository $gazetteRepository*/
    private $gazetteRepository;
    /** @var  ScholarshipCategoryADetailsRepository $scholarshipCategoryADetails*/
    private $scholarshipCategoryADetailsRepository;

    public function __construct(
        ScholarshipCategoryAExamRepository $scholarshipCategoryAExamRepository,
        YearRepository $yearRepository,
        EducationBoardRepository $educationBoardRepository,
        GazetteFileRepository $gazetteFileRepository,
        FileService $fileService,
        GazetteRepository $gazetteRepository,
        ScholarshipCategoryADetailsRepository $scholarshipCategoryADetailsRepository
    ) {
        $this->scholarshipCategoryAExamRepository = $scholarshipCategoryAExamRepository;
        $this->yearRepository = $yearRepository;
        $this->educationBoardRepository = $educationBoardRepository;
        $this->gazetteFileRepository = $gazetteFileRepository;
        $this->fileService = $fileService;
        $this->gazetteRepository = $gazetteRepository;
        $this->scholarshipCategoryADetailsRepository = $scholarshipCategoryADetailsRepository;
    }

    public function getAllExamName()
    {
        try {
            $catAScholarshipExamNames = $this->scholarshipCategoryAExamRepository->all();
            return response()->json($catAScholarshipExamNames);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while fetching exam names',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getAllYears()
    {
        try {
            $years = $this->yearRepository->all();
            return response()->json($years);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while fetching years',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getAllBoards()
    {
        try {
            $years = $this->educationBoardRepository->all();
            return response()->json($years);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while fetching education boards',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function storeGazetteFile($input)
    {
        try {
            $gazetteFile = $this->gazetteFileRepository->create($input);
            return response()->json($gazetteFile);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while saving Gazette File',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getGazetteList($searchCriteria)
    {
        try {
            $listOfGazette = $this->gazetteFileRepository->allWithUser($searchCriteria);
            // Reverse the collection before processing
            $listOfGazette = $listOfGazette->reverse();
            foreach ($listOfGazette as &$gazette) {
                if (isset($gazette['soft_file_id'])) {
                    $excelFile = $this->fileService->retrieve($gazette['soft_file_id']);
                    if (is_array($excelFile)) {
                        $gazette['excel_file_path'] = $excelFile['path'];
                    } else {
                        $statusCheck = $excelFile->getStatusCode();
                        if ($statusCheck == 200) {
                            $gazette['excel_file_path'] = $excelFile['path'];
                        }
                    }
                }
                if (isset($gazette['hard_file_id'])) {
                    $pdfFile = $this->fileService->retrieve($gazette['hard_file_id']);
                    if (is_array($pdfFile)) {
                        $gazette['pdf_file_path'] = $pdfFile['path'];
                    } else {
                        $statusCheck = $pdfFile->getStatusCode();
                        if ($statusCheck == 200) {
                            $gazette['pdf_file_path'] = $pdfFile['path'];
                        }
                    }
                }
            }
            return response()->json($listOfGazette);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while retrieving the list of Gazettes!',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function approve($gazetteId)
    {
        try {
            $dataToUpdate = [];
            $dataToUpdate['is_final'] = 1;
            $dataToUpdate['approved_by'] = Auth::user()->id;
            $gazette = $this->gazetteFileRepository->update($dataToUpdate, $gazetteId);
            return response()->json($gazette);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while retrieving the list of Gazettes!',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function submitForApproval($gazetteId)
    {
        try {
            $dataToUpdate = [];
            $dataToUpdate['is_submitted'] = 1;
            $gazette = $this->gazetteFileRepository->update($dataToUpdate, $gazetteId);
            return response()->json($gazette);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while retrieving the list of Gazettes!',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getGazetteDetails($gazetteId)
    {
        $searchCriteria = [];
        $searchCriteria['file_id'] = $gazetteId;
        try {
            $gazette = $this->gazetteRepository->all($searchCriteria);
            return $gazette;
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while fetching education boards',
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function getListofScholarshipDetails($searchCriteria)
    {
        try {
            $listofScholarshipDetails = $this->scholarshipCategoryADetailsRepository->all($searchCriteria);
            if ($listofScholarshipDetails->isEmpty()) {
                return response()->json([
                    'error' => 'No matching records found',
                    'message' => 'Scholarship Gazette Data not setup. Initiate Scholarship first!'
                ], 404);
            }
            return response()->json($listofScholarshipDetails, 200);
        } catch (Exception $ex) {
            return response()->json([
                'error' => 'An error occurred while fetching data',
                'message' => $ex->getMessage()
            ], 500);
        }
    }
}
