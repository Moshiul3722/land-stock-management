<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_bn',
        'email',
        'mobile',
        'role_id',
        'usertype_id',
        'department_id',
        'password',
        'profile_photo_path',
        'designation',
        'signature_path',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the role of the user.
     */
    /**
     * The signature image as a data URI (for the printed slip and the profile page),
     * or null when the user has not uploaded one.
     */
    public function signatureDataUri(): ?string
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if (! $this->signature_path || ! $disk->exists($this->signature_path)) {
            return null;
        }

        $mime = str_ends_with(strtolower($this->signature_path), '.png') ? 'image/png' : 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($disk->get($this->signature_path));
    }

    public function role(): HasOne
    {
        return $this->hasOne(CnfRole::class, 'created_by');
    }

    public function approvalDetails(): HasMany
    {
        return $this->hasMany(ApprovalDetail::class, 'to_user_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
