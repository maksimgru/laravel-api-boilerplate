<?php

namespace App\Http\Requests;

use App\Constants\RouteConstants;

class PaginationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->rulesPagination(parent::rules());
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->messagesPagination(parent::messages());
    }

    /**
     * Get the Default validation rules that apply to the request.
     *
     * @param array|null $rules
     *
     * @return array
     */
    public function rulesPagination(?array $rules = []): array
    {
        return array_merge(
            $rules,
            [
                config('repository.criteria.params.page')         => 'filled|integer|min:1',
                config('repository.criteria.params.perPage')      => 'filled|integer|min:1',
                config('repository.criteria.params.search')       => 'filled|string',
                config('repository.criteria.params.searchFields') => 'filled|string',
                config('repository.criteria.params.searchJoin')   => 'filled|string|in:' . implode(',', RouteConstants::AVAILABLE_SEARCH_JOIN),
                config('repository.criteria.params.orderBy')      => 'filled|string|in:' . implode(',', static::$model::getOrderable()),
                config('repository.criteria.params.sortedBy')     => 'filled|string|in:' . implode(',', RouteConstants::AVAILABLE_SORT_ORDER_DIRECTIONS),
                config('repository.criteria.params.select')       => [
                    'filled',
                    'string',
                    sprintf('regex:/^(%1$s)*(;(%1$s))*$/i', implode('|', static::$model::getSelectable())),
                ],
                config('repository.criteria.params.with')         => [
                    'filled',
                    'string',
                    sprintf('regex:/^(%1$s)*(;(%1$s))*$/i', implode('|', static::$model::getAvailableWith())),
                ],
                config('repository.criteria.params.withCount')    => [
                    'filled',
                    'string',
                    sprintf('regex:/^(%1$s)*(;(%1$s))*$/i', implode('|', static::$model::getAvailableWith())),
                ],
                config('repository.cache.params.skipCache')       => 'boolean|nullable',
            ]
        );
    }

    /**
     * Get the Default error messages for the defined validation rules.
     *
     * @param array|null $messages
     *
     * @return array
     */
    public function messagesPagination(?array $messages = []): array
    {
        return array_merge($messages, [
            config('repository.criteria.params.searchJoin') . '.in'   => sprintf(trans('validation.in'), implode(' | ', RouteConstants::AVAILABLE_SEARCH_JOIN)),
            config('repository.criteria.params.orderBy') . '.in'      => sprintf(trans('validation.in'), implode(' | ', static::$model::getOrderable())),
            config('repository.criteria.params.sortedBy') . '.in'     => sprintf(trans('validation.in'), implode(' | ', RouteConstants::AVAILABLE_SORT_ORDER_DIRECTIONS)),
            config('repository.criteria.params.select') . '.regex'    => sprintf(trans('validation.regex_in'), implode(' | ', static::$model::getSelectable())),
            config('repository.criteria.params.with') . '.regex'      => sprintf(trans('validation.regex_in'), implode(' | ', static::$model::getAvailableWith())),
            config('repository.criteria.params.withCount') . '.regex' => sprintf(trans('validation.regex_in'), implode(' | ', static::$model::getAvailableWith())),
        ]);
    }
}
