<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\DeleteMediaRequest;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\Models\Media;

class MediaController extends Controller
{
    public static $model = Media::class;

    /**
     * Delete Media
     *
     * @SWG\Delete(
     *  path="/admin/media/{media_id}",
     *  tags={"Admin/Media"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="media_id",
     *     description="Media ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=204, description="No content"),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param DeleteMediaRequest $deleteMediaRequest
     * @param int                $mediaId
     *
     * @return Response
     * @throws HttpException
     */
    public function deleteMedia(
        DeleteMediaRequest $deleteMediaRequest,
        $mediaId
    ): Response {
        return $this->delete($mediaId);
    }
}
