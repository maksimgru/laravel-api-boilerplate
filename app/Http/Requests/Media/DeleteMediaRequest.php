<?php

namespace App\Http\Requests\Media;

use App\Constants\RoleConstants;
use App\Http\Requests\Request;
use App\Models\VisitPlace;
use Illuminate\Validation\Rule;

class DeleteMediaRequest extends Request
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
            'media' => [
                'required',
                'integer',
                Rule::exists('media', 'id'),
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
