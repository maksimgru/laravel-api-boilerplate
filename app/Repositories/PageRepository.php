<?php

namespace App\Repositories;

use App\Models\Page;
use App\Repositories\Criteria\ByCustomCriteria;
use App\Repositories\Criteria\OnlyTrashedCriteria;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PageRepository.
 *
 * @package namespace App\Repositories;
 */
class PageRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Page::class;
    }

    /**
     * @param array $data
     *
     * @return self
     * @throws RepositoryException
     */
    public function applyCustomCriteria(array $data = []): self {
        if (!empty($data['only_trashed'])) {
            $this->withoutGlobalScope(SoftDeletingScope::class);
            $this->pushCriteria(new OnlyTrashedCriteria());
        }

        $criteria = [];
        if (!empty($data['id'])) {
            $criteria['id'] = $data['id'];
        }
        if ($criteria) {
            $this->pushCriteria(new ByCustomCriteria($criteria));
        }

        return $this;
    }
}
