<?php

namespace App\Exceptions\Validation;

use App\Exceptions\ApiException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class NotAllowedDeleteException.
 */
class NotAllowedDeleteException extends ApiException
{
    public $httpStatusCode = SymfonyResponse::HTTP_FORBIDDEN;
    public $message = 'Not allowed delete.';
}
