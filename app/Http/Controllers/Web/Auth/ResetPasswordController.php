<?php

namespace App\Http\Controllers\Web\Auth;

use App\Constants\RouteConstants;
use App\Http\Controllers\Web\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Reset the given user's password.
     *
     * @param CanResetPassword $user
     * @param string           $password
     *
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @return string
     */
    public function redirectTo(): string
    {
        return route(RouteConstants::ROUTE_NAME_WEB_LOGIN);
    }
}
