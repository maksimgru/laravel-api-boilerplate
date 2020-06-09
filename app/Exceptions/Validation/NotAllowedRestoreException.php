<?php

namespace App\Exceptions\Validation;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class NotAllowedRestoreException.
 */
class NotAllowedRestoreException extends ApiException
{
    public $httpStatusCode = SymfonyResponse::HTTP_FORBIDDEN;
    public $message = 'Not allowed restore.';
}
