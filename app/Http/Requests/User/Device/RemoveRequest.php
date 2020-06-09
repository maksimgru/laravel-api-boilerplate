<?php

namespace App\Http\Requests\User\Device;

use App\Http\Requests\Request;

/**
 * Class RemoveRequest
 */
class RemoveRequest extends Request
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
            'device_token' => 'required',
        ];
    }
}
