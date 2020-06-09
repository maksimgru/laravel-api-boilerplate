<?php

namespace App\Policies;

use App\Constants\RoleConstants;
use App\Exceptions\Validation\NotAllowedUpdateException;
use App\Exceptions\Validation\NotAllowedViewException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserPolicy extends BasePolicy
{
    /**
     * @param User|null     $authUser
     * @param User|int|null $targetUser
     *
     * @return bool
     * @throws ModelNotFoundException
     * @throws NotAllowedViewException
     */
    public function view(
        User $authUser = null,
        $targetUser = null
    ): bool {
        $targetUserId = $targetUser instanceof User ? $targetUser->getKey() : (int) $targetUser;

        if (!$authUser || !$targetUserId) {
            throw new NotAllowedViewException();
        }

        if ($authUser->isAdmin() || $authUser->getKey() === $targetUserId) {
            return true;
        }

        if (User::getPrimaryRoleNameByUserId($targetUserId) === RoleConstants::ROLE_BUSINESS) {
            if (\in_array($targetUserId, $authUser->getBusinessIDsByCurrentUser(), true)) {
                return true;
            } else {
                throw new NotAllowedViewException(trans('Not allowed view target user.'));
            }
        }

        if (User::getPrimaryRoleNameByUserId($targetUserId) === RoleConstants::ROLE_WORKER) {
            if (\in_array($targetUserId, $authUser->getWorkersIDsByCurrentUser(), true)) {
                return true;
            } else {
                throw new NotAllowedViewException(trans('Not allowed view target user.'));
            }
        }

        if (User::getPrimaryRoleNameByUserId($targetUserId) === RoleConstants::ROLE_TOURIST) {
            return true;
        }

        throw new NotAllowedViewException(trans('Not allowed view target user.'));
    }

    /**
     * @return bool
     */
    public function create(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * @return bool
     */
    public function restore(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * @param User|null     $authUser
     * @param User|int|null $targetUser
     *
     * @return bool
     * @throws NotAllowedUpdateException
     * @throws ModelNotFoundException
     * @throws NotAllowedViewException
     */
    public function update(
        User $authUser = null,
        $targetUser = null
    ): bool {
        $targetUserId = $targetUser instanceof User ? $targetUser->getKey() : (int) $targetUser;

        if (!$authUser || !$targetUserId) {
            throw new NotAllowedUpdateException();
        }

        if ($authUser->isAdmin() || $authUser->getKey() === $targetUserId) {
            return true;
        }

        if (User::getPrimaryRoleNameByUserId($targetUserId) === RoleConstants::ROLE_BUSINESS) {
            if (\in_array($targetUserId, $authUser->getBusinessIDsByCurrentUser(), true)) {
                return true;
            } else {
                throw new NotAllowedUpdateException(trans('Not allowed update target user.'));
            }
        }

        if (User::getPrimaryRoleNameByUserId($targetUserId) === RoleConstants::ROLE_WORKER) {
            if (\in_array($targetUserId, $authUser->getWorkersIDsByCurrentUser(), true)) {
                return true;
            } else {
                throw new NotAllowedUpdateException(trans('Not allowed update target user.'));
            }
        }

        throw new NotAllowedUpdateException(trans('Not allowed update target user.'));
    }
}
