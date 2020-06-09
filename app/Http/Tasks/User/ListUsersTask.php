<?php

namespace App\Http\Tasks\User;

use App\Http\Tasks\Task;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ListUsersTask
 *
 * @package App\Http\Tasks\User
 */
class ListUsersTask extends Task
{
    /**
     * @var UserRepository $userRepository
     */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @param array $data
     *
     * @return Paginator
     * @throws RepositoryException
     * @throws \InvalidArgumentException
     */
    public function run(array $data): ?Paginator
    {
        return $this
            ->userRepository
            ->applyCustomCriteria($data)
            ->paginate($this->getItemsPerPage($data))
        ;
    }
}
