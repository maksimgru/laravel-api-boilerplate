<?php

namespace App\Http\Requests\User;

use App\Constants\RoleConstants;
use App\Http\Requests\Request;
use App\Models\Role;
use Illuminate\Validation\Rule;

class GetActiveTouristUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query
                        ->where('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_TOURIST))
                        ->where('is_active', true)
                    ;
                }),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => trans('validation.exists'),
        ];
    }
}
