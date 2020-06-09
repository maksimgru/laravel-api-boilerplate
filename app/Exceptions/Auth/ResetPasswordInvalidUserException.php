<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResetPasswordInvalidUserException.
 */
class ResetPasswordInvalidUserException extends ApiException
{

    public $httpStatusCode = Response::HTTP_BAD_REQUEST;

    public $message = 'Invalid user for reset password';
}
