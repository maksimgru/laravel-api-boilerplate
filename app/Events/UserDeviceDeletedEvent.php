<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserDeviceDeletedEvent
{
    use SerializesModels;

    /**
     * @var array $userDevice
     */
    public $userDeviceData;

    /**
     * Create a new event instance.
     *
     * @param array $userDeviceData
     */
    public function __construct(array $userDeviceData)
    {
        $this->userDeviceData = $userDeviceData;
    }
}
