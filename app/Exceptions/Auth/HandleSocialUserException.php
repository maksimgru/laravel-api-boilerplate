<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EmailNotVerifiedException.
 */
class HandleSocialUserException extends ApiException
{

    public $httpStatusCode = Response::HTTP_BAD_REQUEST;

    public $message = 'Provider don\'t handle social user!';
}
