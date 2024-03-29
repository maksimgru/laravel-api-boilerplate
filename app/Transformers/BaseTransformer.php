<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use League\Fractal\TransformerAbstract;
use Specialtactics\L5Api\APIBoilerplate;
use App\Models\BaseModel as RestfulModel;

class BaseTransformer extends TransformerAbstract
{
    /**
     * @var RestfulModel The model to be transformed
     */
    protected $model = null;

    /**
     * Transform an object into a jsonable array
     *
     * @param mixed $object
     *
     * @return array
     * @throws \Exception
     */
    public function transform($object): array
    {
        if (\is_object($object) && $object instanceof RestfulModel) {
            $transformed = $this->transformRestfulModel($object);
        } elseif (\is_object($object) && $object instanceof \stdClass) {
            $transformed = $this->transformStdClass($object);
        } else {
            throw new \RuntimeException('Unexpected object type encountered in transformer');
        }

        return $transformed;
    }

    /**
     * Transform an arbitrary stdClass
     *
     * @param \stdClass $object
     *
     * @return array
     */
    public function transformStdClass($object)
    {
        $transformed = (array) $object;

        /**
         * Transform all keys to correct case, recursively
         */
        $transformed = $this->transformKeysCase($transformed);

        return $transformed;
    }

    /**
     * Transform an eloquent object into a jsonable array
     *
     * @param RestfulModel $model
     *
     * @return array
     */
    public function transformRestfulModel(RestfulModel $model)
    {
        $this->model = $model;

        // Begin the transformation!
        $transformed = $model->toArray();

        /**
         * Filter out attributes we don't want to expose to the API
         */
        $filterOutAttributes = $this->getFilteredOutAttributes();

        $transformed = array_filter($transformed, function ($key) use ($filterOutAttributes) {
            return ! \in_array($key, $filterOutAttributes);
        }, ARRAY_FILTER_USE_KEY);

        /*
         * Format all dates as Iso8601 strings, this includes the created_at and updated_at columns
         * But if exists custom config for date\datetime formats and attributes hasCasts(date or datetime), than it will be applied
         */
        foreach ($model->getDates() as $dateColumn) {
            if (! empty($model->$dateColumn) && ! \in_array($dateColumn, $filterOutAttributes)) {
                if ($model->hasCast($dateColumn, 'date') && config('formats.date')) {
                    $transformed[$dateColumn] = $model->$dateColumn->format(config('formats.date'));
                } elseif ($model->hasCast($dateColumn, 'datetime') && config('formats.datetime') ) {
                    $transformed[$dateColumn] = $model->$dateColumn->format(config('formats.datetime'));
                } else {
                    $transformed[$dateColumn] = $model->$dateColumn->toIso8601String();
                }
            }
        }

        /*
         * Primary Key transformation - all PKs to be called "id"
         */
        unset($transformed[$model->getKeyName()]);

        $transformed = array_merge(
            ['id' => $model->getKey()],
            $transformed
        );

        if (method_exists($this->model, 'getPropertyAttributeValues')) {
            $transformed['properties'] = $this->model->getPropertyAttributeValues();
        }

        /*
         * Transform the model keys' case
         */
        $transformed = $this->transformKeysCase($transformed);

        /**
         * Get the relations for this object and transform them
         */
        $transformed = $this->transformRelations($transformed);


        return $transformed;
    }

    /**
     * Transform the keys of the object to the correct case as required
     *
     * @param array $transformed
     *
     * @return array $transformed
     */
    protected function transformKeysCase(array $transformed)
    {
        // Get the config for how many levels of keys to transform
        $levels = config('api.formatsOptions.transform_keys_levels', null);

        /**
         * Transform all keys to required case, to the specified number of levels (by default, infinite)
         */
        $transformed = APIBoilerplate::formatKeyCaseAccordingToResponseFormat($transformed, $levels);

        /*
         * However, if the level is 1, we also want to transform the first level of keys in a json field which has been cast to array
         */
        if ($levels == 1 && ! is_null($this->model)) {
            // Go through casted atrributes, figuring out what their new key name is to access them
            foreach ($this->model->getCasts() as $fieldName => $castType) {
                if ($castType == 'array') {
                    $fieldNameFormatted = APIBoilerplate::formatKeyCaseAccordingToResponseFormat($fieldName);

                    // Transform the keys of the array attribute
                    if (array_key_exists($fieldNameFormatted, $transformed) && ! empty($transformed[$fieldNameFormatted])) {
                        $transformed[$fieldNameFormatted] = APIBoilerplate::formatKeyCaseAccordingToResponseFormat($transformed[$fieldNameFormatted]);
                    }
                }
            }
        }

        return $transformed;
    }

    /**
     * Formats case of the input array or scalar to desired case
     *
     * @deprecated
     * @param array|string $input
     * @param int|null $levels How many levels of an array keys to transform - by default recurse infiniately (null)
     *
     * @return array $transformed
     */
    protected function formatKeyCase($input, $levels = null)
    {
        return APIBoilerplate::formatKeyCaseAccordingToResponseFormat($input, $levels);
    }

    /**
     * Filter out some attributes immediately
     *
     * Some attributes we never want to expose to an API consumer, for security and separation of concerns reasons
     * Feel free to override this function as necessary
     *
     * @return array Array of attributes to filter out
     */
    protected function getFilteredOutAttributes()
    {
        $filterOutAttributes = array_merge(
            $this->model->getHidden(),
            [
                $this->model->getKeyName(),
                'deleted_at',
            ]
        );

        return array_unique($filterOutAttributes);
    }

    /**
     * Do relation transformations
     *
     * @param array $transformed
     *
     * @return array $transformed
     */
    protected function transformRelations(array $transformed)
    {
        // Iterate through all relations
        foreach ($this->model->getRelations() as $relationKey => $relation) {
            $transformedRelationKey = APIBoilerplate::formatKeyCaseAccordingToResponseFormat($relationKey);

            // Skip Pivot
            if ($relation instanceof Pivot) {
                continue;
            }

            // Transform Collection
            elseif ($relation instanceof Collection) {
                if (\count($relation->getIterator()) > 0) {
                    $relationModel = $relation->first();
                    $relationTransformer = method_exists($relationModel, 'getTransformer')
                        ? $relationModel::getTransformer()
                        : null
                    ;

                    // Transform related model collection
                    if ($relationTransformer && $this->model->$relationKey) {
                        // Create empty array for relation
                        $transformed[$transformedRelationKey] = [];

                        foreach ($relation->getIterator() as $key => $relatedModel) {
                            // Replace the related models with their transformed selves
                            $transformedRelatedModel = $relationTransformer->transform($relatedModel);

                            // We don't really care about pivot information at this stage
                            if (isset($transformedRelatedModel['pivot'])) {
                                unset($transformedRelatedModel['pivot']);
                            }

                            // Add transformed model to relation array
                            $transformed[$transformedRelationKey][] = $transformedRelatedModel;
                        }
                    }
                }
            }

            // Transformed related model
            elseif ($relation instanceof RestfulModel) {
                // Get transformer of relation model
                $relationTransformer = $relation::getTransformer();

                if ($this->model->$relationKey) {
                    $transformed[$transformedRelationKey] = $relationTransformer->transform($this->model->$relationKey);
                }
            }
        }

        return $transformed;
    }
}
