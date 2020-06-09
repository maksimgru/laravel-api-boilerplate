<?php

namespace App\Http\Tasks\Page;

use App\Constants\MediaLibraryConstants;
use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Tasks\Task;
use App\Models\Page;
use App\Services\RestfulService;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Database\QueryException;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class CreatePageTask
 *
 * @package App\Http\Tasks\Page
 */
class CreatePageTask extends Task
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
     * @return Page|null
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws ValidationFailedException
     * @throws StoreResourceFailedException
     */
    public function run(
        array $data,
        string $model
    ): ?Page {
        $this->restfulService->setModel($model);
        /** @var MessageBag $errors */
        $errors = $this->restfulService->validateResourceModel($data);
        $newPage = $this->restfulService->getModelInstance()->setValidationErrors($errors->getMessages());

        if (!$errors->getMessages()) {
            /** @var Page $newPage */
            $newPage = $this->restfulService->persistResourceModel($data);
            $newPage = $newPage->getFullModel($newPage->getKey());

            if (isset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_MAIN_IMAGE])) {
                $newPage->handleUploadedMedia(
                    MediaLibraryConstants::REQUEST_FIELD_NAME_MAIN_IMAGE,
                    MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE
                );
            }

            if (isset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY])) {
                $newPage->handleUploadedMultipleMedia(
                    [MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY],
                    MediaLibraryConstants::COLLECTION_NAME_GALLERY
                );
            }
        }

        return $newPage;
    }
}
