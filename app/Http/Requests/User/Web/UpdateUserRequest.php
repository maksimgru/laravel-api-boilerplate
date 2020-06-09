<?php

namespace App\Http\Requests\User\Web;

use App\Http\Requests\Request;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;

class UpdateUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Gate $gate
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function authorize(Gate $gate): bool
    {
        return $gate->getPolicyFor(User::class)->update($this->user(), (int) $this->user_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_id' => 'integer|exists:users,id',
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
