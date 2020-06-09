<?php

namespace App\Http\Tasks\User;

use App\Http\Tasks\Task;
use App\Models\User;

/**
 * Class RemoveFavoriteVisitPlaceTask
 *
 * @package App\Http\Tasks\User
 */
class RemoveFavoriteVisitPlaceTask extends Task
{
    /**
     * @param int  $visitPlaceId
     * @param User $user
     *
     * @return User|null
     * @throws \Throwable
     */
    public function run(
        int $visitPlaceId,
        User $user
    ): User {
        return $user->detachFavoriteVisitPlace($visitPlaceId);
    }
}
