<?php

namespace App\Repositories\Criteria;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class OnlyTrashedCriteria.
 *
 * @package namespace App\Repositories\Criteria;
 */
class OnlyTrashedCriteria extends Criteria
{
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
        return $model->whereNotNull('deleted_at');
    }
}

