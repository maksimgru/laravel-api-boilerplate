<?php

namespace App\Exceptions;

use Dingo\Api\Contract\Debug\MessageBagErrors as DingoMessageBagErrors;
use Exception;
use Illuminate\Support\MessageBag;
use Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;

/**
 * Class ApiException.
 */
abstract class ApiException extends SymfonyHttpException implements DingoMessageBagErrors
{

    /**
     * MessageBag errors.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * Default status code.
     *
     * @var int
     */
    protected $defaultHttpStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @var string
     */
    protected $environment;

    /**
     * ApiException constructor.
     *
     * @param null            $message
     * @param null            $errors
     * @param null            $statusCode
     * @param int             $code
     * @param \Exception|null $previous
     * @param array           $headers
     */
    public function __construct(
        $message = null,
        $errors = null,
        $statusCode = null,
        $code = 0,
        Exception $previous = null,
        $headers = []
    ) {
        $this->environment = config('app.env');

        if (null === $message && property_exists($this, 'message')) {
            $message = $this->message;
        }

        if (null === $errors) {
            $this->errors = new MessageBag();
        } else {
            $this->errors = \is_array($errors) ? new MessageBag($errors) : $errors;
        }

        if (null === $statusCode) {
            if (property_exists($this, 'httpStatusCode')) {
                $statusCode = $this->httpStatusCode;
            } else {
                $statusCode = $this->defaultHttpStatusCode;
            }
        }

        if ($this->environment != 'testing') {
            Log::error('[ERROR] ' .
                       'Status Code: ' . $statusCode . ' | ' .
                       'Message: ' . $message . ' | ' .
                       'Errors: ' . $this->errors . ' | ' .
                       'Code: ' . $code
            );
        }

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * Help developers debug the error without showing these details to the end user.
     * Usage: `throw (new MyCustomException())->debug($e)`.
     *
     * @param $error
     *
     * @return self
     */
    public function debug($error): self
    {
        if ($error instanceof Exception) {
            $error = $error->getMessage();
        }

        if ($this->environment != 'testing') {
            Log::error('[DEBUG] ' . $error);
        }

        return $this;
    }

    /**
     * Get the errors message bag.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Determine if message bag has any errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !$this->errors->isEmpty();
    }
}
