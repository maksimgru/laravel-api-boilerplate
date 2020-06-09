<?php

namespace App\Http\Requests\Page;

use App\Http\Requests\Request;
use App\Models\Page;
use Illuminate\Validation\Rule;

class DeletePageMediaRequest extends Request
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
                Rule::exists('media', 'id')->where(function ($query) {
                    $query->where(['model_type' => Page::class]);
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
