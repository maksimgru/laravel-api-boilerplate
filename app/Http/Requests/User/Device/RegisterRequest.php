<?php

namespace App\Http\Requests\User\Device;

use App\Constants\UserDevicesConstants;
use App\Http\Requests\Request;

/**
 * Class RegisterRequest
 */
class RegisterRequest extends Request
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
            'device_id'    => 'required',
            'device_type'  => 'required|in:' . implode(',', UserDevicesConstants::$allowedDeviceTypes),
        ];
    }
}
