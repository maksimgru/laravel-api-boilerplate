<?php

namespace App\Http\Tasks\Auth;

use App\Exceptions\Auth\RestorePasswordErrorException;
use App\Exceptions\Auth\UserNotActivatedException;
use App\Http\Tasks\Task;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class RestorePasswordTask
 *
 * @package App\Http\Tasks\Auth
 */
class RestorePasswordTask extends Task
{
    /**
     * @var LoginTask
     */
    protected $loginTask;

    /**
     * Create new RestorePasswordTask instance.
     *
     * @param LoginTask      $loginTask
     */
    public function __construct(
        LoginTask $loginTask
    ) {
        $this->loginTask = $loginTask;
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws UserNotActivatedException
     * @throws RestorePasswordErrorException
     * @throws UnauthorizedHttpException
     * @throws AccountNotVerifiedException
     * @throws ErrorRestorePasswordException
     */
    public function run(array $data): ?string
    {
        $credentials = [
            'email'                 => $data['email'] ?? null,
            'password'              => $data['password'] ?? null,
            'password_confirmation' => $data['password_confirm'] ?? null,
            'token'                 => $data['reset_password_token'] ?? null,
        ];

        $response = Password::reset($credentials, function ($user, $password) {
            $hashedPassword = Hash::make($password);
            $user->password = $hashedPassword;
            $user->save();
        });

        if ($response !== Password::PASSWORD_RESET) {
            $errorMessage = $response === Password::INVALID_TOKEN
                ? trans('passwords.token')
                : trans('passwords.restore-error')
            ;
            throw new RestorePasswordErrorException($errorMessage);
        }

        return $this->loginTask->run([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ]);
    }
}
