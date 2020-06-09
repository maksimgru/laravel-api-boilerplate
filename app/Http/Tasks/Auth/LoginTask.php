<?php

namespace App\Http\Tasks\Auth;

use App\Exceptions\Auth\UserNotActivatedException;
use App\Http\Requests\User\AuthRequest;
use App\Http\Tasks\Task;
use App\Models\User;
use Dingo\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class LoginTask
 *
 * @package App\Http\Tasks\Auth
 */
class LoginTask extends Task
{
    /**
     * @param mixed $data
     *
     * @return null|string
     * @throws UserNotActivatedException
     * @throws UnauthorizedHttpException
     */
    public function run($data): ?string
    {
        if ($data instanceof User) {
            $token = auth()->login($data);
        } else {
            if (\is_array($data)) {
                $credentials = $data;
            }
            if ($data instanceof AuthRequest) {
                $credentials = $data->validated();
            }
            $token = auth()->attempt($credentials);
        }

        if (auth()->user() && !auth()->user()->isActive()) {
            auth()->logout();
            throw new UserNotActivatedException(trans('auth.user.not_activated'));
        }

        if (!$token) {
            throw new UnauthorizedHttpException(
                trans('auth.user.unauthorized'),
                null,
                null,
                Response::HTTP_BAD_REQUEST
            );
        }

        return $token;
    }
}
