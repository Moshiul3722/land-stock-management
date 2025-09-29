<?php

namespace App\Services;

use App\Repositories\StudentsApplicationFormRepository;
use App\Repositories\StudentApplicationStatusRepository;

class StApplicationStatusService
{

    protected $studentApplicationStatusRepository;
    protected $studentsApplicationFormRepository;

    public function __construct(StudentApplicationStatusRepository $studentApplicationStatusRepository, StudentsApplicationFormRepository $studentsApplicationFormRepository)
    {
        $this->studentApplicationStatusRepository = $studentApplicationStatusRepository;
        $this->studentsApplicationFormRepository = $studentsApplicationFormRepository;
    }

    /**
     * Change application status by st_app_id.
     *
     * @param int $stAppId
     * @param int $newStatus
     * @return mixed
     */
    // public function changeStatus(int $stAppId, int $newStatus)
    // {
    //     $status = $this->studentApplicationStatusRepository->model()::where(['st_app_id' => $stAppId])->first();

    //     if ($status) {
    //         $status->app_status = $newStatus;
    //         return $this->studentApplicationStatusRepository->update($status->toArray(), $status->id);
    //     }

    //     return null; // Handle case where status with st_app_id not found
    // }

    public function updateApplicationStatus($id, $status)
    {
        $applicationStatus = $this->studentApplicationStatusRepository->model()::where('st_app_id', $id)->first();
        if ($applicationStatus) {
            $applicationStatus->update(['app_status' => $status]);
        }
    }
    /**
     * Get application forms along with their statuses.
     *
     * @return mixed
     */
    public function getApplicationFormsWithStatuses()
    {
        $forms = $this->studentsApplicationFormRepository->withRelations();
        $formsWithStatus = $forms->map(function ($form) {
            $status = $this->studentApplicationStatusRepository->model()::where(['st_app_id' => $form->id])->first();
            $form->app_status = $status ? $status->app_status : null;
            return $form;
        });

        return $formsWithStatus->sortBy('app_status');
    }

    public function getApplicationCounts()
{
    try {
        $allCount = $this->studentApplicationStatusRepository->countAll();
        $primarilySelectedCount = $this->studentApplicationStatusRepository->countByStatus(1); // Replace with your logic
        $finalSelectedCount = $this->studentApplicationStatusRepository->countByStatus(2); // Replace with your logic

        return [
            'all' => $allCount,
            'primarily_selected' => $primarilySelectedCount,
            'final_selected' => $finalSelectedCount,
        ];
    } catch (\Exception $e) {
        throw new \Exception("Error fetching application counts: " . $e->getMessage());
    }
}

}