<?php

namespace App\Http\Tasks\User;

use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Tasks\Task;
use App\Models\User;
use App\Services\RestfulService;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class AddFavoriteVisitPlacesTask
 *
 * @package App\Http\Tasks\User
 */
class AddFavoriteVisitPlacesTask extends Task
{
    /**
     * @var RestfulService $restfulService
     */
    protected $restfulService;

    /**
     * @param RestfulService $restfulService
     */
    public function __construct(RestfulService $restfulService)
    {
        $this->restfulService = $restfulService;
    }

    /**
     * @param array $data
     * @param User  $user
     *
     * @return User|null
     * @throws \Throwable
     * @throws ValidationFailedException
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws StoreResourceFailedException
     */
    public function run(
        array $data,
        User $user
    ): User {
        $this->restfulService->validateResourceModelUpdate($user, $data);

        return $user->attachFavoriteVisitPlaces($data['favorite_visit_places']);
    }
}
