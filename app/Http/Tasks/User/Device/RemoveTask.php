<?php

namespace App\Http\Tasks\User\Device;

use App\Events\UserDeviceDeletedEvent;
use App\Http\Tasks\Task;
use App\Models\User;
use App\Models\UserDevice;

/**
 * Class RemoveTask
 *
 * @package App\Http\Tasks\User
 */
class RemoveTask extends Task
{
    /**
     * @param string    $deviceToken
     * @param User|null $user
     *
     * @return int
     */
    public function run(
        string $deviceToken,
        ?User $user = null
    ): int {
        $userDevices = UserDevice::where('device_token', $deviceToken)->get();
        $deleted = 0;
        foreach ($userDevices as $userDevice) {
            $deletedCurrent = $userDevice->delete();
            if (!($deletedCurrent < 1)) {
                event(new UserDeviceDeletedEvent($userDevice->toArray()));
                $deleted += (bool) $deletedCurrent;
            }
        }

        return $deleted;
    }
}
