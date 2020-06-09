<?php

namespace App\Listeners;

use App\Events\UserDeviceDeletedEvent;
use App\Services\SnsPush\SnsPushAdapter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\SNSPushException;

class RemoveDeviceOnAmazonSide implements ShouldQueue
{
    /**
     * @var SnsPushAdapter $snsPushAdapter
     */
    protected $snsPushAdapter;

    /**
     * @param SnsPushAdapter $snsPushAdapter
     */
    public function __construct(SnsPushAdapter $snsPushAdapter)
    {
        $this->snsPushAdapter = $snsPushAdapter;
    }

    /**
     * Handle the event.
     *
     * @param UserDeviceDeletedEvent $event
     *
     * @return void
     * @throws SNSPushException
     * @throws InvalidArnException
     * @throws \InvalidArgumentException
     */
    public function handle(UserDeviceDeletedEvent $event)
    {
        /** @var array $userDeviceData */
        $userDeviceData = $event->userDeviceData;

        if (!empty($userDeviceData['arn_endpoint'])) {
            try {
                $this->snsPushAdapter->removeDevice($userDeviceData['arn_endpoint']);
            } catch (\Exception $e) {
                \Log::error('Error remove device endpoint on Amazon Side',
                    [
                        'user_id'      => $userDeviceData['user_id'],
                        'device_type'  => $userDeviceData['device_type'],
                        'arn_endpoint' => $userDeviceData['arn_endpoint'],
                        'error'        => $e->getMessage(),
                    ]
                );
            }
        }
    }
}
