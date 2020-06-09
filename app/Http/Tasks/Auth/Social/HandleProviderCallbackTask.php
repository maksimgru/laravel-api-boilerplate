<?php

namespace App\Http\Tasks\Auth\Social;

use App\Exceptions\Auth\HandleSocialUserException;
use App\Http\Controllers\Front\UserController;
use App\Http\Tasks\Auth\LoginTask;
use App\Http\Tasks\Task;
use App\Http\Tasks\User\RegisterUserTask;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialUser;

/**
 * Class HandleProviderCallbackTask
 *
 * @package App\Http\Tasks\Auth\Social
 */
class HandleProviderCallbackTask extends Task
{
    /**
     * @var LoginTask
     */
    protected $loginTask;

    /**
     * @var RegisterUserTask
     */
    protected $registerUserTask;

    public function __construct(
        LoginTask $loginTask,
        RegisterUserTask $registerUserTask
    ) {
        $this->loginTask = $loginTask;
        $this->registerUserTask = $registerUserTask;
    }

    /**
     * @param string      $provider
     * @param string|null $token
     *
     * @return string|null
     * @throws HandleSocialUserException
     */
    public function run(
        string $provider,
        string $token = null
    ): ?string {
        $socialDriver = Socialite::driver($provider)->stateless();
        try {
            $user = $token ? $socialDriver->userFromToken($token) : $socialDriver->user();
        } catch (ClientException $e) {
            throw new HandleSocialUserException($e->getMessage());
        }
        $authUser = $this->findOrCreateUser($user, $provider);

        return $this->loginTask->run($authUser);
    }

    /**
     * @param SocialUser $socialUser
     * @param string $provider
     *
     * @return User
     */
    protected function findOrCreateUser(
        SocialUser $socialUser,
        string $provider
    ): User {
        $keyProviderId = $provider . '_id';
        $user = User::where($keyProviderId, $socialUser->getId())->first();

        if ($user) {
            return $user;
        }

        /** @var User $user */
        $user = User::whereEmail($socialUser->getEmail())->first();
        if ($user) {
            $user->update([
                $keyProviderId => $socialUser->getId()
            ]);
        } else {
            $user = $this->registerUserTask->run(
                [
                    'email' => $socialUser->getEmail(),
                    'username' => $socialUser->getName(),
                    'password' => generateRandomString(),
                    $keyProviderId => $socialUser->getId(),
                ],
                UserController::$model
            );
        }

        return $user;
    }
}
