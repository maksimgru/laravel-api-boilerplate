<?php

namespace App\Exceptions\Validation;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class ValidationFailedException.
 */
class ValidationFailedException extends ApiException
{
    public $httpStatusCode = SymfonyResponse::HTTP_BAD_REQUEST;
    public $message = 'Invalid Input.';
}
