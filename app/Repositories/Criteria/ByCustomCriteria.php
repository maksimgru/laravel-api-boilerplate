<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class ByCustomCriteria.
 *
 * @package namespace App\Repositories\Criteria;
 */
class ByCustomCriteria extends Criteria
{
    /**
     * @var array
     */
    protected $criteria;

    /**
     * @param array $criteria
     */
    public function __construct(array $criteria) {
        $this->criteria = $criteria;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if (!$this->criteria) {return $model;}

        foreach ($this->criteria as $name => $value) {
            if (\is_array($value)) {
                $model->whereIn($name, $value);
            } else {
                $model->where($name, $value);
            }
        }

        return $model;
    }
}

