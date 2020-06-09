<?php

namespace App\Exceptions\Auth;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EmailNotVerifiedException.
 */
class EmailNotVerifiedException extends ApiException
{

    public $httpStatusCode = Response::HTTP_BAD_REQUEST;

    public $message = 'Email not verified!';
}
