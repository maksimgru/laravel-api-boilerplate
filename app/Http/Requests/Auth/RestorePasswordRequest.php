<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class RestorePasswordRequest extends Request
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
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|same:password_confirm',
            'reset_password_token' => 'required',
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
            'email.required' => trans('auth.email.required'),
            'email.email' => trans('auth.email.invalid'),
            'email.exists' => trans('auth.email.exists'),
        ];
    }
}
