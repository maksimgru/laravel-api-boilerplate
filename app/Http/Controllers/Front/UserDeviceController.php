<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Device\RegisterRequest;
use App\Http\Requests\User\Device\RemoveRequest;
use App\Http\Tasks\User\Device\RegisterTask;
use App\Http\Tasks\User\Device\RemoveTask;
use App\Models\UserDevice;
use Dingo\Api\Http\Response;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\SNSPushException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class UserDeviceController
 *
 * @package App\Http\Controllers\Front
 */
class UserDeviceController extends Controller
{
    public static $model = UserDevice::class;

    /**
     * @SWG\Post(
     *  path="/devices/register",
     *  tags={"Users/Devices"},
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
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         @SWG\Property(property="device_token", type="string", example="d0B-9J_E2us:APA91bH8rPsE1gHvEM4AiXgNSFex5G6-OdkqaWqojBSJHaHJmQjZaBOlSXqqtuLDoD7SxkairQ2-TBmAKhEO9-iqITVXYl11sKQBcJAvN0g-JySSZqrg_eTeyOO6eZtHjCEB2ggJ_Msp", description="required"),
     *         @SWG\Property(property="device_type", type="string", example="android", description="required:ios|android"),
     *         @SWG\Property(property="device_id", type="string", example="2beab2b8a137dfe3", description="required"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=202, description="Accepted"),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     * )
     *
     * @param RegisterRequest $registerRequest
     * @param RegisterTask    $registerTask
     *
     * @return Response
     * @throws SNSPushException
     * @throws InvalidArnException
     * @throws \InvalidArgumentException
     */
    public function register(
        RegisterRequest $registerRequest,
        RegisterTask $registerTask
    ): Response {
        $userDevice = $registerTask->run(
            $this->user(),
            $registerRequest->input('device_token'),
            $registerRequest->input('device_id'),
            $registerRequest->input('device_type')
        );

        return $this->response
            ->item($userDevice, $this->getTransformer())
            ->setStatusCode($userDevice->getKey() ? Response::HTTP_ACCEPTED : Response::HTTP_BAD_REQUEST)
        ;
    }

    /**
     * @SWG\Post(
     *  path="/devices/remove",
     *  tags={"Users/Devices"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         @SWG\Property(property="device_token", type="string", example="string", description="required"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=204, description="NoContent"),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=404, description="Not Found"),
     * )
     *
     * @param RemoveRequest $removeRequest
     * @param RemoveTask    $removeTask
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function remove(
        RemoveRequest $removeRequest,
        RemoveTask $removeTask
    ): Response {
        $deletedCount = $removeTask->run(
            $removeRequest['device_token'],
            $this->user()
        );

        if ($deletedCount < 1) {
            throw new NotFoundHttpException('Could not find a UserDevice with that DeviceToken to delete');
        }

        return $this->response->noContent();
    }
}
