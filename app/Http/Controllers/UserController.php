<?php

namespace App\Http\Controllers;

use Flash;
use Response;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\ServiceControllers\RoleServiceController;
use App\Http\Controllers\ServiceControllers\UserServiceController;
use Illuminate\Support\Facades\Log;
use App\Models\CnfRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends AppBaseController
{
    /** @var RoleServiceController $roleServiceController*/
    private $roleServiceController;
    /** @var UserServiceController $userServiceController*/
    private $userServiceController;

    public function __construct(
        RoleServiceController $roleServiceController,
        UserServiceController $userServiceController
    ) {
        $this->roleServiceController = $roleServiceController;
        $this->userServiceController = $userServiceController;
    }


    /** Role that must belong to a section/department (General User). */
    private const DEPARTMENT_REQUIRED_ROLE_ID = 5;

    /**
     * Display the list of all users. Create, edit and delete happen in modals on this page.
     */
    public function index()
    {
        $users = User::query()
            ->whereNull('deleted_at')
            ->with('department:id,name,name_bn')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $roles = CnfRole::orderBy('role_name_en')->get(['id', 'role_name_en', 'role_name_bn']);
        $departments = Department::orderBy('name')->get(['id', 'name', 'name_bn']);

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    /**
     * Sidebar "create user" menu: opens the create modal on the list page.
     */
    public function create()
    {
        return redirect()->route('users.index', ['create' => 1]);
    }

    /**
     * Store a newly created User.
     */
    public function store(Request $request)
    {
        $data = $this->validateUser($request);

        try {
            $attributes = [
                'name' => $data['name'],
                'name_bn' => $data['name_bn'] ?? null,
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
                'role_id' => $data['role_id'],
                'department_id' => $data['department_id'] ?? null,
                'password' => Hash::make($data['password']),
            ];

            // E-mail of a user that was removed earlier: bring that record back with the
            // new details (its history stays attached) rather than adding a duplicate.
            $removed = User::where('email', $data['email'])->whereNotNull('deleted_at')->first();

            if ($removed) {
                $removed->forceFill($attributes + [
                    'is_active' => 1,
                    'deleted_at' => null,
                    'deleted_by' => null,
                    'updated_by' => Auth::id(),
                    // Count as newly added, so the user shows at the top of the list.
                    'created_at' => now(),
                ])->save();

                return response()->json(['id' => $removed->id, 'restored' => true]);
            }

            $user = User::create($attributes + ['created_by' => Auth::id()]);

            return response()->json(['id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('User create failed: ' . $e->getMessage());
            return response()->json(['error' => trans('user.save_failed')], 500);
        }
    }

    /**
     * Old edit page URL: opens the edit modal on the list page.
     */
    public function edit($id)
    {
        return redirect()->route('users.index', ['edit' => (int) $id]);
    }

    /**
     * Update the specified User.
     */
    public function update($id, Request $request)
    {
        $user = User::whereNull('deleted_at')->find($id);

        if (empty($user)) {
            return response()->json(['error' => trans('flash.no_record_found')], 404);
        }

        $data = $this->validateUser($request, $user);

        // Changing your own role could lock you out of this very screen.
        if ((int) $user->id === (int) Auth::id() && (int) $data['role_id'] !== (int) $user->role_id) {
            return response()->json([
                'message' => trans('user.cannot_change_own_role'),
                'errors' => ['role_id' => [trans('user.cannot_change_own_role')]],
            ], 422);
        }

        try {
            $user->name = $data['name'];
            $user->name_bn = $data['name_bn'] ?? null;
            $user->email = $data['email'];
            $user->mobile = $data['mobile'] ?? null;
            $user->role_id = $data['role_id'];
            $user->department_id = $data['department_id'] ?? null;
            $user->updated_by = Auth::id();
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            return response()->json(['id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('User update failed: ' . $e->getMessage());
            return response()->json(['error' => trans('user.save_failed')], 500);
        }
    }

    /**
     * Remove a user from the system.
     *
     * The record is kept (requisitions and approvals refer to it); it is marked deleted and
     * inactive, disappears from the lists and can no longer sign in.
     */
    public function destroy($id)
    {
        $user = User::whereNull('deleted_at')->find($id);

        if (empty($user)) {
            return response()->json(['error' => trans('flash.no_record_found')], 404);
        }

        if ((int) $user->id === (int) Auth::id()) {
            return response()->json(['error' => trans('user.cannot_delete_self')], 422);
        }

        try {
            $user->forceFill([
                'is_active' => 0,
                'deleted_at' => now(),
                'deleted_by' => Auth::id(),
                'remember_token' => null,
            ])->save();

            return response()->json(['success' => 'deleted']);
        } catch (\Exception $e) {
            Log::error('User delete failed: ' . $e->getMessage());
            return response()->json(['error' => trans('user.delete_failed')], 500);
        }
    }

    /**
     * Shared validation for create and update. On update the password is optional.
     */
    private function validateUser(Request $request, ?User $user = null): array
    {
        // Stored as 01XXXXXXXXX whatever way it was typed (Bangla digits, +88, spaces); an
        // empty field clears the number.
        $mobile = trim((string) $request->input('mobile'));
        $request->merge(['mobile' => $mobile === ''
            ? null
            : (\App\Http\Controllers\Auth\OtpLoginController::normaliseMobile($mobile) ?? $mobile)]);

        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'name_bn' => ['nullable', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                'max:191',
                // The same person must not be added twice. Removed users keep their record
                // (and e-mail), so they get their own message.
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $existing = User::where('email', $value)
                        ->when($user, fn($query) => $query->where('id', '!=', $user->id))
                        ->first();

                    if (! $existing) {
                        return;
                    }

                    // Adding a previously removed user again is allowed: store() brings
                    // that record back instead of creating a second one.
                    if ($existing->deleted_at && $user === null) {
                        return;
                    }

                    $fail(trans($existing->deleted_at ? 'user.email_taken_deleted' : 'user.email_taken'));
                },
            ],
            // The number the user signs in with (a login code is sent to it): optional, a
            // Bangladeshi mobile number, and not shared with another user.
            'mobile' => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (\App\Http\Controllers\Auth\OtpLoginController::normaliseMobile((string) $value) === null) {
                        $fail(trans('user.mobile_invalid'));

                        return;
                    }

                    $taken = User::where('mobile', $value)
                        ->whereNull('deleted_at')
                        ->when($user, fn($query) => $query->where('id', '!=', $user->id))
                        ->exists();

                    if ($taken) {
                        $fail(trans('user.mobile_taken'));
                    }
                },
            ],
            'role_id' => ['required', 'integer', Rule::exists('cnf_role', 'id')->whereNull('deleted_at')],
            'department_id' => [
                'nullable',
                'required_if:role_id,' . self::DEPARTMENT_REQUIRED_ROLE_ID,
                'integer',
                Rule::exists('departments', 'id')->whereNull('deleted_at'),
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'max:191'],
            'confirm_password' => ['required_with:password', 'same:password'],
        ], [
            'department_id.required_if' => trans('user.department_required'),
            'password.required' => trans('user.password_required'),
            'password.min' => trans('user.password_min'),
            'email.email' => trans('user.email_invalid'),
            'confirm_password.same' => trans('user.password_match'),
            'confirm_password.required_with' => trans('user.confirm_password_empty'),
        ], [
            'name' => trans('user.user_name_en'),
            'name_bn' => trans('user.user_name_bn'),
            'email' => trans('user.email'),
            'mobile' => trans('user.mobile'),
            'role_id' => trans('user.role'),
            'department_id' => trans('user.section_department'),
            'password' => trans('user.password'),
            'confirm_password' => trans('user.confirm_password'),
        ]);
    }
}
