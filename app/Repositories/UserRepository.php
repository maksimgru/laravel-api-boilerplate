<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Criteria\ByCustomCriteria;
use App\Repositories\Criteria\OnlyTrashedCriteria;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class UserRepository.
 *
 * @package namespace App\Repositories;
 */
class UserRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return User::class;
    }

    /**
     * @param array $data
     *
     * @return self
     * @throws RepositoryException
     */
    public function applyCustomCriteria(array $data = []): self
    {
        if (!empty($data['only_trashed'])) {
            $this->withoutGlobalScope(SoftDeletingScope::class);
            $this->pushCriteria(new OnlyTrashedCriteria());
        }

        $criteria = [];
        if (isset($data['id'])) {
            $criteria['users.id'] = $data['id'];
        }
        if (isset($data['primary_role_id'])) {
            $criteria['users.primary_role_id'] = $data['primary_role_id'];
        }
        if (isset($data['manager_id'])) {
            $criteria['users.manager_id'] = $data['manager_id'];
        }
        if (isset($data['business_id'])) {
            $criteria['users.business_id'] = $data['business_id'];
        }
        if (isset($data['is_active'])) {
            $criteria['users.is_active'] = $data['is_active'];
        }
        if ($criteria) {
            $this->pushCriteria(new ByCustomCriteria($criteria));
        }

        return $this;
    }
}
