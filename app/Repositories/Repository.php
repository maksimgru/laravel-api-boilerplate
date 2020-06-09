<?php

namespace App\Repositories;

use App\Repositories\Criteria\ByRequestCriteria;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\CacheableInterface as PrettusCacheableInterface;
use Prettus\Repository\Eloquent\BaseRepository as PrettusRepository;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Repository\Traits\CacheableRepository as PrettusCacheableRepository;

/**
 * Class Repository.
 */
abstract class Repository extends PrettusRepository implements PrettusCacheableInterface
{
    use PrettusCacheableRepository;

    /**
     * Boot up the repository, pushing criteria.
     * @throws RepositoryException
     */
    public function boot()
    {
        $this->pushCriteria(app(ByRequestCriteria::class));
    }

    /**
     * Get the authenticated user.
     *
     * @return mixed
     */
    public function user()
    {
        return app(\Auth::class)->user();
    }

    /**
     * @param \Closure $chunk
     * @param int      $limit
     *
     * @throws RepositoryException
     */
    public function chunk(\Closure $chunk, $limit = 200)
    {
        $this->applyCriteria();
        $this->applyScope();
        /** @var Builder $model */
        $model = $this->model;
        $model->chunk($limit, $chunk);
        $this->resetModel();
        $this->resetScope();
    }
}
