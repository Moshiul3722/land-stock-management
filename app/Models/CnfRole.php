<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class CnfRole
 * @package App\Models
 * @version March 28, 2024, 6:54 am UTC
 *
 * @property string $role_name_en
 * @property string $role_name_bn
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class CnfRole extends Model
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'cnf_role';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'role_name_en',
        'role_name_bn',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'role_name_en' => 'string',
        'role_name_bn' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'role_name_en' => 'nullable|string|max:255',
        'role_name_bn' => 'nullable|string|max:255',
        'created_at' => 'required',
        'created_by' => 'nullable|integer',
        'updated_at' => 'required',
        'updated_by' => 'nullable|integer',
        'deleted_at' => 'required',
        'deleted_by' => 'nullable|integer'
    ];

    /**
     * Get the user who created the role.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
