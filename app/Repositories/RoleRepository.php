<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Criteria\ByCustomCriteria;
use App\Repositories\Criteria\OnlyTrashedCriteria;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class RoleRepository.
 *
 * @package namespace App\Repositories;
 */
class RoleRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Role::class;
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
