<?php

namespace App\Repositories\Criteria;

use App\Constants\RouteConstants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class ByRequestCriteria.
 */
class ByRequestCriteria extends RequestCriteria
{
    /**
     * Apply criteria in query repository
     *
     * @param Builder             $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     * @throws \Exception
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $model->getModel()::getSearchable();
        $fieldsSearchable = $fieldsSearchable ?: $repository->getFieldsSearchable();
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $select = $this->request->get(config('repository.criteria.params.select', 'select'), null);
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), RouteConstants::DEFAULT_ORDER_BY);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), RouteConstants::DEFAULT_SORT_ORDER_DIRECTIONS);
        $sortedBy = $sortedBy ?: RouteConstants::DEFAULT_SORT_ORDER_DIRECTIONS;
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $withCount = $this->request->get(config('repository.criteria.params.withCount', 'withCount'), null);
        $searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), RouteConstants::DEFAULT_SEARCH_JOIN);

        $model = $this->applySearchCriteria(
            $model,
            $search,
            $fieldsSearchable,
            $searchFields,
            $searchJoin
        );

        $model = $this->applyOrderByCriteria(
            $model,
            $orderBy,
            $sortedBy
        );

        $model = $this->applySelectCriteria(
            $model,
            $select
        );

        $model = $this->applyWithCriteria(
            $model,
            $with
        );

        $model = $this->applyWithCountCriteria(
            $model,
            $withCount
        );

        return $model;
    }

    /**
     * @param Builder           $model
     * @param string|null       $search
     * @param array|null        fieldsSearchable
     * @param array|string|null $searchFields
     * @param string|null       $searchJoin
     *
     * @return mixed
     */
    protected function applySearchCriteria(
        $model,
        $search,
        $fieldsSearchable,
        $searchFields,
        $searchJoin
    ) {
        if ($search && \is_array($fieldsSearchable) && \count($fieldsSearchable)) {
            $searchFields = (\is_array($searchFields) || null === $searchFields)
                ? $searchFields
                : explode(';', $searchFields)
            ;
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $search = $this->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';
            $model = $model->where(
                function ($query) use (
                    $fields,
                    $search,
                    $searchData,
                    $isFirstField,
                    $modelForceAndWhere
                ) {
                    /** @var Builder $query */
                    foreach ($fields as $field => $condition) {
                        if (is_numeric($field)) {
                            $field = $condition;
                            $condition = '=';
                        }
                        $value = null;
                        $condition = strtolower(trim($condition));
                        if (isset($searchData[$field])) {
                            $value = ($condition == 'like' || $condition == 'ilike')
                                ? "%{$searchData[$field]}%"
                                : $searchData[$field]
                            ;
                        } else {
                            if (null !== $search) {
                                $value = ($condition == 'like' || $condition == 'ilike')
                                    ? "%{$search}%"
                                    : $search
                                ;
                            }
                        }
                        $relation = null;
                        if (strpos($field, '.')) {
                            $explode = explode('.', $field);
                            $field = array_pop($explode);
                            $relation = implode('.', $explode);
                        }
                        $modelTableName = $query->getModel()->getTable();
                        if ($isFirstField || $modelForceAndWhere) {
                            if (null !== $value) {
                                if (null !== $relation) {
                                    $query->whereHas($relation,
                                        function ($query) use ($field, $condition, $value)
                                        {
                                            $query->where($field, $condition, $value);
                                        }
                                    );
                                } else {
                                    $query->where(
                                        $modelTableName . '.' . $field,
                                        $condition,
                                        $value
                                    );
                                }
                                $isFirstField = false;
                            }
                        } else {
                            if (null !== $value) {
                                if (null !== $relation) {
                                    $query->orWhereHas($relation,
                                        function ($query) use ($field, $condition, $value)
                                        {
                                            $query->where($field, $condition, $value);
                                        }
                                    );
                                } else {
                                    $query->orWhere(
                                        $modelTableName . '.' . $field,
                                        $condition,
                                        $value
                                    );
                                }
                            }
                        }
                    }
                }
            );
        }

        return $model;
    }

    /**
     * @param Builder     $model
     * @param string|null $orderBy
     * @param string|null $sortedBy
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function applyOrderByCriteria(
        $model,
        $orderBy,
        $sortedBy
    ) {
        if (!empty($orderBy)) {
            $split = explode(';', $orderBy);
            if (\count($split) > 1) {
                /*
                 * ex.
                 * products;description
                 * JOIN "products" ON current_table.product_id = products.id ORDERBY "description"
                 *
                 * products..custom_id;products.description
                 * JOIN "products" ON current_table.custom_id = products.id ORDERBY "products.description"
                 * (in case both tables have same column name)
                 */
                $table = $model->getModel()
                    ->getTable()
                ;
                $sortTable = $split[0];
                $sortColumn = $split[1];
                $split = explode('..', $sortTable);
                if (\count($split) > 1) {
                    $sortTable = $split[0];
                    $keyName = $table . '.' . $split[1];
                } else {
                    /*
                     * If you do not define which column to use as a joining column on current table, it will
                     * use a singular of a join table appended with _id
                     *
                     * ex.
                     * products -> product_id
                     */
                    $prefix = Str::singular($sortTable);
                    $keyName = $table . '.' . $prefix . '_id';
                }
                $model = $model
                    ->leftJoin("{$sortTable} as sortable_{$sortTable}", $keyName, '=', "sortable_{$sortTable}.id")
                    ->orderBy('sortable_' . $sortColumn, $sortedBy)
                    ->addSelect($table . '.*')
                ;
            } else {
                $model = $model->orderBy($orderBy, $sortedBy);
            }
        }

        return $model;
    }

    /**
     * @param Builder     $model
     * @param string|null $select
     *
     * @return mixed
     */
    protected function applySelectCriteria(
        $model,
        $select
    ) {
        if (!empty($select)) {
            if (\is_string($select)) {
                $select = explode(';', $select);
            }
            $model = $model->select($select);
        }

        return $model;
    }

    /**
     * @param Builder     $model
     * @param string|null $with
     *
     * @return mixed
     */
    protected function applyWithCriteria(
        Builder $model,
        $with
    ) {
        $defaultCollectionWith = $model->getModel()::getCollectionWith();
        $with = $with
            ? array_unique(array_merge(explode(';', $with), $defaultCollectionWith))
            : $defaultCollectionWith
        ;
        if ($with) {
            $model = $model->with($with);
        }

        return $model;
    }

    /**
     * @param Builder     $model
     * @param string|null $withCount
     *
     * @return mixed
     */
    protected function applyWithCountCriteria(
        $model,
        $withCount
    ) {
        $withCount = $withCount
            ? explode(';', $withCount)
            : []
        ;
        if ($withCount) {
            $model = $model->withCount($withCount);
        }

        return $model;
    }
}
