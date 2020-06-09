<?php

namespace App\Http\Tasks\User;

use App\Constants\MediaLibraryConstants;
use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Tasks\Task;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;
use App\Services\RestfulService;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Database\QueryException;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class RegisterUserTask
 *
 * @package App\Http\Tasks\User
 */
class RegisterUserTask extends Task
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
     * @param array  $data
     * @param string $model
     *
     * @return User|null
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws ValidationFailedException
     * @throws StoreResourceFailedException
     */
    public function run(
        array $data,
        string $model
    ): ?User {
        $this->restfulService->setModel($model);
        /** @var MessageBag $errors */
        $errors = $this->restfulService->validateResourceModel($data);
        $newUser = $this->restfulService->getModelInstance()->setValidationErrors($errors->getMessages());

        if (!$errors->getMessages()) {
            /** @var User $newUser */
            $newUser = $this->restfulService->persistResourceModel($data);
            $newUser = $newUser->getFullModel($newUser->getKey());

            if (isset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR])) {
                $newUser->handleUploadedMedia(
                    MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR,
                    MediaLibraryConstants::COLLECTION_NAME_AVATAR
                );
                unset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR]);
            }

            // Email notify new user
            \Notification::send($newUser, new UserRegisteredNotification($newUser, $data));
        }

        return $newUser->setIsNew(true);
    }
}
