<?php

namespace App\Http\Requests;

use App\Models\BaseModel;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\Validation\ValidationFailedException;
use Illuminate\Foundation\Http\FormRequest;
use Jenssegers\Agent\Agent;

class Request extends FormRequest
{
    /**
     * Specify the model that you want to be associated with this request.
     *
     * @var \App\Models\BaseModel $model
     */
    public static $model = BaseModel::class;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->setModelFromCtrl();

        return [];
    }

    /**
     * overriding the failedValidation function to throw my custom
     * exception instead of the default Laravel exception.
     *
     * @param Validator $validator
     *
     * @return mixed|void
     * @throws ValidationFailedException
     */
    public function failedValidation(Validator $validator)
    {
        if (isApiRequest(request())) {
            throw new ValidationFailedException(trans('validation.failed'), $validator->getMessageBag());
        }

        return $validator->getMessageBag();
    }

    /**
     * @param array|mixed|null $keys
     *
     * @return array
     * @throws \LogicException
     */
    public function all($keys = null)
    {
        // Add route parameters to validation data
        return array_merge(parent::all($keys), $this->route()->parameters());
    }

    /**
     * @return Agent
     */
    public function getAgent(): Agent {
        return app('agent');
    }

    /**
     * @return bool
     */
    public function isOriginLocalHost(): bool {
        return false !== stripos($this->header('origin'), '://localhost');
    }

    /**
     * @param array $options
     *
     * @return array
     * @throws \LogicException
     */
    public function getSanitizedInputs(array $options = []): array {
        $defaultOptions = [
            'data'    => [], // request inputs
            'only'    => [], // names of leaved request fields
            'except'  => [], // names of excluded request fields
            'numeric' => false, // convert numeric field in number format
        ];
        $options = array_merge($defaultOptions, $options);
        $data = $options['data'] ?: $this->all();

        foreach ($data as $key => $value) {
            // Only
            if ($options['only']
                && !\in_array($key, (array) $options['only'], true)
            ) {
                unset($data[$key]);
                continue;
            }
            // Except
            if ($options['except']
                && \in_array($key, (array) $options['except'], true)
            ) {
                unset($data[$key]);
                continue;
            }
            // Sanitize numeric
            if ($options['numeric']) {
                if (is_numeric($value)) {
                    $value += 0;
                } elseif (\is_array($value)) {
                    $value = $this->getSanitizedInputs(['data' => $value, 'numeric' => $options['numeric']]);
                }
            }
            // Set new sanitized value
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @param null|string $model
     *
     * @return BaseModel
     */
    public function setModelFromCtrl(?string $model = null): BaseModel {
        $controller = $this->route()->getController();
        $model = $model ?? $controller::$model;

        return static::$model = $model ? new $model : new static::$model;
    }
}
