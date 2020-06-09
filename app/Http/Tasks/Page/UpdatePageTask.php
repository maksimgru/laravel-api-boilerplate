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
 * Class UpdatePageTask
 *
 * @package App\Http\Tasks\Page
 */
class UpdatePageTask extends Task
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
     * @param Page  $page
     *
     * @return Page|null
     * @throws ValidationFailedException
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws StoreResourceFailedException
     */
    public function run(
        array $data,
        Page $page
    ): Page {
        /** @var MessageBag $errors */
        $errors = $this->restfulService->validateResourceModelUpdate($page, $data);

        if (!$errors->getMessages()) {
            $this->restfulService->updateResource($page, $data);
            if (isset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_MAIN_IMAGE])) {
                $page->handleUploadedMedia(
                    MediaLibraryConstants::REQUEST_FIELD_NAME_MAIN_IMAGE,
                    MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE
                );
            }
            if (isset($data[MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY])) {
                $page->handleUploadedMultipleMedia(
                    [MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY],
                    MediaLibraryConstants::COLLECTION_NAME_GALLERY
                );
            }
        }

        return $page->getFullModel($page->getKey());
    }
}
