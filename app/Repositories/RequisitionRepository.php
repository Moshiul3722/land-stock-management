<?php

namespace App\Repositories;

use App\Models\Requisition;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class RequisitionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'id',
        'requested_by_user_id',
        'requisition_date',
        'is_print_enabled',
        'status',
        'current_serial',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Requisition::class;
    }


    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    /**
     * Get requisition counts grouped by the last few months.
     */
    public function getMonthlyCounts($limit = 6)
    {
        // Group by Month (MySQL syntax)
        return $this->model
            ->select(
                DB::raw("DATE_FORMAT(requisition_date, '%M %Y') as month_name"),
                DB::raw("YEAR(requisition_date) as year"),
                DB::raw("MONTH(requisition_date) as month"),
                DB::raw('COUNT(id) as count')
            )
            ->groupBy('year', 'month', 'month_name')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get requisition counts grouped by department.
     */
    public function getCountsByDepartment()
    {
        return $this->model
            ->join('users', 'requisitions.requested_by_user_id', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->select(
                DB::raw('IFNULL(departments.name, "No Department") as department_name'),
                DB::raw('COUNT(requisitions.id) as count')
            )
            ->groupBy('departments.id', 'departments.name')
            ->get();
    }
}
