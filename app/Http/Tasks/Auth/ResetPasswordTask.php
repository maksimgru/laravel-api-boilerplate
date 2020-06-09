<?php

namespace App\Http\Tasks\Auth;

use App\Exceptions\Auth\ResetPasswordErrorException;
use App\Exceptions\Auth\ResetPasswordInvalidUserException;
use App\Http\Tasks\Task;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;

/**
 * Class ResetPasswordTask
 *
 * @package App\Http\Tasks\Auth
 */
class ResetPasswordTask extends Task
{
    /**
     * @param $email
     *
     * @return bool|null
     * @throws ResetPasswordErrorException
     * @throws ResetPasswordInvalidUserException
     */
    public function run(string $email): ?bool
    {
        $response = Password::sendResetLink(['email' => $email], function ($message) {
            /** @var Message $message */
            $message->from(config('mail.from.address'), config('app.name'));
            $message->subject(trans('passwords.sent'));
        });

        switch ($response) {
            case Password::INVALID_USER:
                throw new ResetPasswordInvalidUserException(trans('passwords.user'));
            case Password::RESET_LINK_SENT:
                return true;
            default:
                throw new ResetPasswordErrorException(trans('passwords.reset-error'));
        }
    }
}
