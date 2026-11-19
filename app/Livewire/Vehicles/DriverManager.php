<?php

namespace App\Livewire\Vehicles;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;

/**
 * Vehicle drivers: list with modal add / edit / view / delete. Each driver gets a sign-in
 * account (role "Vehicle Driver") with their email, used for the demand form. The photo and
 * the licence image are stored outside the public folder (shown through
 * VehicleDriverFileController); the licence number is read from the licence image in the
 * browser and can be corrected by hand.
 */
class DriverManager extends VehicleCrud
{
    use WithFileUploads;

    private const FILE_FOLDER = 'vehicle-drivers';

    public string $nameBn = '';

    public string $name = '';

    public string $mobile = '';

    public string $email = '';

    public string $licenseNo = '';

    /** New uploads (Livewire temporary files) */
    public $photo = null;

    public $license = null;

    /** Files already saved for the driver being edited (for the preview) */
    #[Locked]
    public bool $hasPhoto = false;

    #[Locked]
    public bool $hasLicense = false;

    public bool $showView = false;

    #[Locked]
    public ?int $viewId = null;

    protected function permissionRoutes(): array
    {
        return ['vehicle-drivers.index'];
    }

    protected function modelClass(): string
    {
        return VehicleDriver::class;
    }

    protected function textKey(): string
    {
        return 'drivers';
    }

    #[Computed]
    public function rows()
    {
        return VehicleDriver::query()
            ->with('user:id,is_active')
            ->withCount('vehicles')
            ->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', $this->like())->orWhere('name_bn', 'like', $this->like())
                ->orWhere('mobile', 'like', $this->like())->orWhere('email', 'like', $this->like())
                ->orWhere('license_no', 'like', $this->like())))
            ->orderByDesc('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function viewing(): ?VehicleDriver
    {
        return $this->viewId ? VehicleDriver::with(['vehicles.type', 'vehicles.route', 'user:id,is_active'])->find($this->viewId) : null;
    }

    public function openView(int $id): void
    {
        $this->authorizeAccess();
        if (! VehicleDriver::whereKey($id)->exists()) {
            $this->notify('error', __('commontable.not_found'));

            return;
        }
        $this->viewId = $id;
        unset($this->viewing);
        $this->showView = true;
    }

    protected function fillForm(Model $record): void
    {
        $this->nameBn = (string) $record->name_bn;
        $this->name = (string) $record->name;
        $this->mobile = (string) $record->mobile;
        $this->email = (string) $record->email;
        $this->licenseNo = (string) $record->license_no;
        $this->hasPhoto = (bool) $record->photo_path;
        $this->hasLicense = (bool) $record->license_path;
    }

    protected function resetFields(): void
    {
        $this->reset('nameBn', 'name', 'mobile', 'email', 'licenseNo', 'photo', 'license', 'hasPhoto', 'hasLicense');
    }

    protected function beforeValidate(): void
    {
        foreach (['nameBn', 'name', 'email', 'licenseNo'] as $field) {
            $this->{$field} = trim($this->{$field});
        }
        $this->email = Str::lower($this->email);
        $this->licenseNo = Str::upper(preg_replace('/\s+/', '', $this->licenseNo));
        $this->mobile = MobileNumber::normalise($this->mobile);
    }

    /** The account linked to the driver being edited (its own email / mobile are allowed). */
    private function linkedUserId(): ?int
    {
        return $this->editingId ? VehicleDriver::whereKey($this->editingId)->value('user_id') : null;
    }

    protected function formRules(): array
    {
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

        return [
            'nameBn' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email' => ['required', 'email', 'max:191'],
            'licenseNo' => ['nullable', 'string', 'max:50'],
            'photo' => $image,
            'license' => $image,
        ];
    }

    protected function formLabels(): array
    {
        return [
            'nameBn' => __('vehicle.fields.name_bn'), 'name' => __('vehicle.fields.name_en'),
            'mobile' => __('vehicle.fields.mobile'), 'email' => __('vehicle.fields.email'),
            'licenseNo' => __('vehicle.fields.license_no'), 'photo' => __('vehicle.fields.photo'),
            'license' => __('vehicle.fields.license_image'),
        ];
    }

    protected function formAttributes(): array
    {
        $this->checkAccountIsFree();

        return [
            'name_bn' => $this->nameBn,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'license_no' => $this->licenseNo ?: null,
        ];
    }

    /** The email and mobile must not belong to another (not deleted) account. */
    private function checkAccountIsFree(): void
    {
        $userId = $this->linkedUserId();
        $taken = fn (string $column, string $value) => User::query()
            ->where($column, $value)->whereNull('deleted_at')
            ->when($userId, fn ($q) => $q->where('id', '!=', $userId))
            ->exists();

        $errors = [];
        if ($taken('email', $this->email)) {
            $errors['email'] = __('vehicle.drivers.email_taken');
        }
        if ($this->mobile !== '' && $taken('mobile', $this->mobile)) {
            $errors['mobile'] = __('vehicle.drivers.mobile_taken');
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function afterSave(Model $record, bool $creating): void
    {
        // Photo and licence image: the new file replaces the old one
        foreach (['photo' => 'photo_path', 'license' => 'license_path'] as $input => $column) {
            if ($this->{$input}) {
                $old = $record->{$column};
                $record->{$column} = $this->{$input}->store(self::FILE_FOLDER, 'local');
                if ($old) {
                    Storage::disk('local')->delete($old);
                }
            }
        }

        // The driver's sign-in account (role "Vehicle Driver")
        $account = [
            'name' => $this->name,
            'name_bn' => $this->nameBn,
            'email' => $this->email,
            'mobile' => $this->mobile ?: null,
        ];
        $user = $record->user_id ? User::find($record->user_id) : null;
        if ($user) {
            $user->forceFill($account + ['updated_by' => Auth::id()])->save();
        } else {
            $user = new User();
            $user->forceFill($account + [
                'role_id' => VehicleDriver::roleId(),
                'is_active' => 1,
                'password' => Hash::make(Str::random(40)),
                'created_by' => Auth::id(),
            ])->save();
            $record->user_id = $user->id;
        }

        $record->save();
    }

    protected function recordLabel(Model $record): string
    {
        return (string) ($record->name_bn ?: $record->name);
    }

    protected function blockedDelete(Model $record): ?string
    {
        return Vehicle::where('driver_id', $record->id)->exists() ? __('vehicle.in_use') : null;
    }

    /** The driver's account can no longer sign in once the driver is deleted. */
    protected function beforeDelete(Model $record): void
    {
        if ($record->user_id) {
            DB::table('users')->where('id', $record->user_id)->where('role_id', VehicleDriver::roleId())
                ->update(['is_active' => 0, 'deleted_at' => now(), 'deleted_by' => Auth::id()]);
        }
    }

    public function render()
    {
        return view('livewire.vehicles.driver-manager', $this->pageData() + ['rows' => $this->rows]);
    }
}
