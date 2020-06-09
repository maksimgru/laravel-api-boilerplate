<?php

namespace App\Transformers;

use App\Models\UserDevice;

class UserDeviceTransformer extends BaseTransformer
{
    /**
     * @param UserDevice $userDevice
     *
     * @return array
     * @throws \Exception
     */
    public function transform($userDevice): array
    {
        return parent::transform($userDevice);
    }
}
