<?php

namespace App\Http\Tasks\Role;

use App\Http\Tasks\Task;
use App\Repositories\RoleRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ListRolesTask
 *
 * @package App\Http\Tasks\Role
 */
class ListRolesTask extends Task
{
    /**
     * @var RoleRepository $roleRepository
     */
    protected $roleRepository;

    /**
     * @param RoleRepository $roleRepository
     */
    public function __construct(
        RoleRepository $roleRepository
    ) {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param array $data
     *
     * @return Paginator
     * @throws RepositoryException
     * @throws \InvalidArgumentException
     */
    public function run(array $data): ?Paginator {
        return $this
            ->roleRepository
            ->applyCustomCriteria($data)
            ->paginate($this->getItemsPerPage($data))
        ;
    }
}
