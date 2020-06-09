<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserNotActivatedException.
 */
class UserNotActivatedException extends ApiException
{

    public $httpStatusCode = Response::HTTP_FORBIDDEN;

    public $message = 'User is not activated!';
}
