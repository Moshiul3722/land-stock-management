<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Section / department list (Configuration > User Management).
 * Create, edit and delete happen in modals on the list page and answer with JSON.
 */
class DepartmentController extends AppBaseController
{
    public function index()
    {
        $departments = Department::query()
            ->withCount(['users as active_users_count' => fn($query) => $query->whereNull('deleted_at')])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validateDepartment($request);

        try {
            $department = Department::create($data);

            return response()->json(['id' => $department->id]);
        } catch (\Exception $e) {
            Log::error('Department create failed: ' . $e->getMessage());
            return response()->json(['error' => trans('department.save_failed')], 500);
        }
    }

    public function update($id, Request $request)
    {
        $department = Department::find($id);

        if (empty($department)) {
            return response()->json(['error' => trans('flash.no_record_found')], 404);
        }

        $data = $this->validateDepartment($request, $department);

        try {
            $department->update($data);

            return response()->json(['id' => $department->id]);
        } catch (\Exception $e) {
            Log::error('Department update failed: ' . $e->getMessage());
            return response()->json(['error' => trans('department.save_failed')], 500);
        }
    }

    /**
     * Soft-deletes a department. One that still has users is kept, so nobody is
     * left without a section/department.
     */
    public function destroy($id)
    {
        $department = Department::find($id);

        if (empty($department)) {
            return response()->json(['error' => trans('flash.no_record_found')], 404);
        }

        $inUse = User::where('department_id', $department->id)->whereNull('deleted_at')->exists();
        if ($inUse) {
            return response()->json(['error' => trans('department.cannot_delete_in_use')], 422);
        }

        try {
            $department->delete();

            return response()->json(['success' => 'deleted']);
        } catch (\Exception $e) {
            Log::error('Department delete failed: ' . $e->getMessage());
            return response()->json(['error' => trans('department.delete_failed')], 500);
        }
    }

    private function validateDepartment(Request $request, ?Department $department = null): array
    {
        $unique = fn(string $column) => Rule::unique('departments', $column)
            ->whereNull('deleted_at')
            ->ignore($department?->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', $unique('name')],
            'name_bn' => ['required', 'string', 'max:191', $unique('name_bn')],
            'code' => ['nullable', 'string', 'max:191', $unique('code')],
        ], [], [
            'name' => trans('department.name_en'),
            'name_bn' => trans('department.name_bn'),
            'code' => trans('department.code'),
        ]);

        $data['code'] = ($data['code'] ?? '') !== '' ? $data['code'] : null;

        return $data;
    }
}
