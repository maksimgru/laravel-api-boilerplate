<?php

namespace App\Services;

use App\Exceptions\Validation\ValidationFailedException;
use App\Models\BaseModel;
use Dingo\Api\Exception\StoreResourceFailedException;
use Specialtactics\L5Api\Services\RestfulService as VendorRestfulService;
use Config;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Validator;

/**
 * Class RestfulService
 *
 * Your can extend the RestfulService functionality here
 */
class RestfulService extends VendorRestfulService
{
    /**
     * The Model Class name
     * Get model class name to be used in the service
     *
     * @param
     *
     * @return null|string
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param array|null $attributes
     *
     * @return BaseModel
     */
    public function getModelInstance(?array $attributes = []): BaseModel
    {
        return $this->model ? new $this->model($attributes) : new BaseModel();
    }

    /**
     * Validates a given resource (Restful Model) against a given data set, and throws an API exception on failure
     *
     * @param array $data
     *
     * @return void
     * @throws ValidationFailedException
     * @throws StoreResourceFailedException
     */
    public function validateResourceModel(array $data = null)
    {
        $model = $this->getModelInstance();
        // If no data is provided, validate the resource against it's present attributes
        if (null === $data) {
            $data = $model->getAttributes();
        }

        $validator = Validator::make(
            $data,
            $model->getValidationRules(),
            $model->getValidationMessages()
        );

        if ($validator->fails() && isApiRequest(request())) {
            throw new ValidationFailedException(trans('validation.failed'), $validator->errors());
        }

        return $validator->errors();
    }

    /**
     * Validates a given resource (Restful Model) against a given data set in the update context - ie. validating
     * only the fields updated in the provided data set, and throws an API exception on failure
     *
     * @param BaseModel $model
     * @param array     $data Data we are validating against
     *
     * @return array
     * @throws ValidationFailedException
     * @throws StoreResourceFailedException
     */
    public function validateResourceModelUpdate($model, array $data)
    {
        $validator = Validator::make(
            $data,
            $this->getRelevantValidationRules($model, $data),
            $model->getValidationMessages()
        );

        if ($validator->fails() && isApiRequest(request())) {
            throw new ValidationFailedException(trans('validation.failed'), $validator->errors());
        }

        return $validator->errors();
    }

    /**
     * Create model in the database
     *
     * @param array     $data
     * @param bool|null $withImmutable
     *
     * @return mixed
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     */
    public function persistResourceModel(
        array $data = null,
        ?bool $withImmutable = false
    ): BaseModel {
        return $this->persistResource($this->getModelInstance($data), $withImmutable);
    }

    /**
     * Update model in the database
     *
     * @param BaseModel $model
     * @param array     $data
     * @param bool|null $withImmutable
     *
     * @return bool
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     */
    public function updateResourceModel(
        $model,
        array $data,
        ?bool $withImmutable = false
    ): bool {
        return $this->updateResource($model, $data, $withImmutable);
    }

    /**
     * Create model in the database
     *
     * @param BaseModel $model
     * @param bool|null $withImmutable
     *
     * @return mixed
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     */
    public function persistResource(
        $model,
        ?bool $withImmutable = false
    ) {
        try {
            if ($withImmutable) {
                $model->immutableAttributes = [];
            }
            $model->save();
        } catch (\Exception $e) {
            // Check for QueryException - if so, we may want to display a more meaningful message, or help with
            // development debugging
            if ($e instanceof QueryException) {
                if (stristr($e->getMessage(), 'duplicate')) {
                    throw new ConflictHttpException('The resource already exists: ' . class_basename($model));
                } elseif (Config::get('api.debug') === true) {
                    throw $e;
                }
            }

            // Default HTTP exception to use for storage errors
            $errorMessage = 'Unexpected error trying to store this resource.';

            if (Config::get('api.debug') === true) {
                $errorMessage .= ' ' . $e->getMessage();
            }

            throw new UnprocessableEntityHttpException($errorMessage);
        }

        return $model;
    }

    /**
     * Patch a resource of the given model, with the given request
     *
     * @param BaseModel $model
     * @param array     $data
     * @param bool|null $withImmutable
     *
     * @return bool
     * @throws QueryException
     * @throws ConflictHttpException
     * @throws UnprocessableEntityHttpException
     * @throws HttpException
     */
    public function updateResource(
        $model,
        array $data,
        ?bool $withImmutable = false
    ): bool {
        try {
            if ($withImmutable) {
                $model->immutableAttributes = [];
            }
            $resource = $model->update($data);
        } catch (\Exception $e) {
            // Check for QueryException - if so, we may want to display a more meaningful message, or help with
            // development debugging
            if ($e instanceof QueryException) {
                if (stristr($e->getMessage(), 'duplicate')) {
                    throw new ConflictHttpException('The resource already exists: ' . class_basename($model));
                } elseif (Config::get('api.debug') === true) {
                    throw $e;
                }
            }

            // Default HTTP exception to use for storage errors
            $errorMessage = 'Unexpected error trying to store this resource.';

            if (Config::get('api.debug') === true) {
                $errorMessage .= ' ' . $e->getMessage();
            }

            throw new UnprocessableEntityHttpException($errorMessage);
        }

        return $resource;
    }
}
