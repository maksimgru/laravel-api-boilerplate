<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class DeleteUserMediaRequest extends Request
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
        $authUser = $this->user();
        return [
            'media' => [
                'integer',
                Rule::exists('media', 'id')->where(function ($query) use ($authUser) {
                    if (!$authUser->isAdmin() && !$authUser->isPrimaryRoleManager()) {
                        $query->where([
                            'model_type' => User::class,
                            'model_id' => $authUser->id,
                        ]);
                    }
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
            'media.exists' => trans('validation.exists'),
        ];
    }
}
