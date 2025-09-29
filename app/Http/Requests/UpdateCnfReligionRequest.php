<?php

namespace App\Http\Requests;

use App\Models\CnfReligion;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCnfReligionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = CnfReligion::$rules;
        
        return $rules;
    }
}
