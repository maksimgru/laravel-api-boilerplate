<?php

namespace App\Http\Tasks\User;

use App\Constants\MediaLibraryConstants;
use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Tasks\Task;
use App\Models\User;
use App\Services\RestfulService;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Database\QueryException;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UpdateUserTask
 *
 * @package App\Http\Tasks\User
 */
class UpdateUserTask extends Task
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
     * @return User
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
        if (empty($data['password'])) {
            unset($data['password']);
        }

        /** @var MessageBag $errors */
        $errors = $this->restfulService->validateResourceModelUpdate($user, $data);
        if (!$errors->getMessages()) {
            $this->restfulService->updateResourceModel($user, $data, auth()->user()->isAdmin());
            if (isset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR])) {
                $user->handleUploadedMedia(
                    MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR,
                    MediaLibraryConstants::COLLECTION_NAME_AVATAR
                );
            }
        }

        $user = $user->getFullModel($user->getKey());
        $user->setValidationErrors($errors->getMessages());

        return $user;
    }
}
