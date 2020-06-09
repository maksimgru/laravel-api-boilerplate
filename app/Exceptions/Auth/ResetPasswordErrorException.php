<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResetPasswordErrorException.
 */
class ResetPasswordErrorException extends ApiException
{

    public $httpStatusCode = Response::HTTP_BAD_REQUEST;

    public $message = 'Error on reset password';
}
