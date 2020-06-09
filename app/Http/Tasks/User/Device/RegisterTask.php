<?php

namespace App\Http\Tasks\User\Device;

use App\Http\Tasks\Task;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\SnsPush\SnsPushAdapter;
use SNSPush\ARN\EndpointARN;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\SNSPushException;

/**
 * Class RegisterTask
 * @package App\Http\Tasks\User
 */
class RegisterTask extends Task
{
    /**
     * @var SnsPushAdapter $snsPushAdapter
     */
    protected $snsPushAdapter;

    /**
     * RegisterTask constructor.
     *
     * @param SnsPushAdapter $snsPushAdapter
     */
    public function __construct(
        SnsPushAdapter $snsPushAdapter
    ) {
        $this->snsPushAdapter = $snsPushAdapter;
    }

    /**
     * @param User   $user
     * @param string $deviceToken
     * @param string $deviceId
     * @param string $deviceType [android, ios, etc]
     *
     * @return UserDevice
     * @throws SNSPushException
     * @throws InvalidArnException
     * @throws \InvalidArgumentException
     */
    public function run(
        User $user,
        string $deviceToken,
        string $deviceId,
        string $deviceType
    ): UserDevice {
        try {
            /** @var EndpointARN $deviceArnEndpoint*/
            $deviceArnEndpoint = $this->snsPushAdapter->addDevice($deviceToken, $deviceType);

            // Fields for check
            $attributes = [
                'user_id'     => $user->getKey(),
                'device_id'   => $deviceId,
                'device_type' => UserDevice::getDeviceTypeId($deviceType),
            ];

            // Fields for update
            $values = [
                'device_token' => $deviceToken,
                'arn_endpoint' => $deviceArnEndpoint->toString(),
            ];

            // Check existed userDevice by device_id
            // If not exist than search by device_token
            $existedUserDevice = UserDevice::where($attributes)->first(['id']);
            if (!$existedUserDevice) {
                unset(
                    $attributes['device_id'],
                    $values['device_token']
                );
                $attributes['device_token'] = $deviceToken;
                $values['device_id']        = $deviceId;
                $existedUserDevice          = UserDevice::where($attributes)->first(['id']);
                if (!$existedUserDevice) {
                    $values['device_token'] = $deviceToken;
                }
            }

            // Store deviceToken and deviceID and arnEndpoints into DB
            return UserDevice::updateOrCreate($attributes, $values);
        } catch (\Exception $e) {
            UserDevice::removeByDeviceToken($deviceToken);

            \Log::error('Error during registering User Device: ',
                [
                    'user_id'      => $user->getKey(),
                    'device_type'  => $deviceType,
                    'device_token' => $deviceToken,
                    'error'        => $e->getMessage(),
                ]
            );

            return (new UserDevice)
                ->setValidationErrors([
                    'message' => 'Error during registering User Device: ',
                    'details' => $e->getMessage(),
                ])
            ;
        }
    }
}
