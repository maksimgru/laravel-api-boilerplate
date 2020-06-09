<?php

namespace App\Services\SnsPush;

use App\Models\UserDevice;
use Aws\ApiGateway\Exception\ApiGatewayException;
use Aws\Result;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use SNSPush\ARN\ARN;
use SNSPush\ARN\EndpointARN;
use SNSPush\ARN\TopicARN;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Exceptions\InvalidTypeException;
use SNSPush\Exceptions\SNSPushException;
use SNSPush\Exceptions\UnsupportedPlatformException;
use SNSPush\SNSPush;

/**
 * Class SnsPush
 *
 * Sends a message to an Amazon SNS topic or sends a text message (SMS message) directly to a phone number.
 * If you send a message to a topic, Amazon SNS delivers the message to each endpoint that is subscribed to the topic.
 * The format of the message depends on the notification protocol for each subscribed endpoint.
 * When a messageId is returned, the message has been saved and Amazon SNS will attempt to deliver it shortly.
 * To use the Publish action for sending a message to a mobile endpoint, such as an app on a Kindle device or mobile phone,
 * you must specify the EndpointArn for the TargetArn parameter.
 * The EndpointArn is returned when making a call with the CreatePlatformEndpoint action.
 *
 * @package Hello\Services\SnsPushAdapter
 */
class SnsPushAdapter extends SNSPush
{
    protected const API_GATEWAY_EXCEPTION_MESSAGE = 'There was an unknown problem with the AWS SNS API. Code: ';

    /**
     * @return SnsClient
     */
    public function getClient(): SnsClient
    {
        return $this->client;
    }

    /**
     * @return array
     */
    public function getPlatformsArns(): array
    {
        return $this->config['platform_applications'];
    }

    /**
     * Creates a topic to which notifications can be published.
     * Users can create at most 100,000 topics.
     * This action is idempotent, so if the requester already owns a topic with the specified name,
     * that topic's ARN is returned without creating a new topic.
     *
     * @param string $topicName
     * @param array  $options
     *
     * @return Result|bool
     * @throws SNSPushException
     */
    public function createTopic(string $topicName, array $options = [])
    {
        try {
            $result = $this->client->createTopic(array_merge(
                [
                    'Name' => $topicName,
                ],
                $options
            ));

            return $result['TopicArn'] ? $result : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException(self::API_GATEWAY_EXCEPTION_MESSAGE . $e->getCode());
        }
    }

    /**
     * Deletes a topic and all its subscriptions.
     * Deleting a topic might prevent some messages previously sent to the topic from being delivered to subscribers.
     * This action is idempotent, so deleting a topic that does not exist does not result in an error.
     *
     * @param TopicARN|string $topicArn
     * @param array  $options
     *
     * @return Result|bool
     * @throws SNSPushException
     */
    public function deleteTopic($topicArn, array $options = [])
    {
        $topicArn = $topicArn instanceof TopicARN ? $topicArn : TopicARN::parse($topicArn);
        try {
            $result = $this->client->deleteTopic(array_merge(
                [
                    $topicArn->getKey() => $topicArn->toString(),
                ],
                $options
            ));

            return $result['TopicArn'] ? $result : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException(self::API_GATEWAY_EXCEPTION_MESSAGE . $e->getCode());
        }
    }

    /**
     * Returns a list of the subscriptions to a specific topic.
     * Each call returns a limited list of subscriptions, up to 100.
     * If there are more subscriptions, a NextToken is also returned.
     * Use the NextToken parameter in a new ListSubscriptionsByTopic call to get further results.
     * This action is throttled at 30 transactions per second (TPS).
     *
     * @param TopicARN|string $topicArn
     * @param array  $options
     *
     * @return Result|bool
     * @throws SNSPushException
     */
    public function getSubscriptionsByTopic($topicArn, array $options = [])
    {
        $topicArn = $topicArn instanceof TopicARN ? $topicArn : TopicARN::parse($topicArn);
        try {
            $result = $this->client->listSubscriptionsByTopic(array_merge(
                [
                    $topicArn->getKey() => $topicArn->toString(),
                ],
                $options
            ));

            return $result['Subscriptions'] ? $result : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException(self::API_GATEWAY_EXCEPTION_MESSAGE . $e->getCode());
        }
    }

    /**
     * Returns a list of the requester's topics.
     * Each call returns a limited list of topics, up to 100.
     * If there are more topics, a NextToken is also returned. Use the NextToken parameter in a new ListTopics call to get further results.
     * This action is throttled at 30 transactions per second (TPS).
     *
     * @return Result|bool
     * @throws SNSPushException
     */
    public function getAllTopics()
    {
        try {
            $result = $this->client->listTopics();
            return $result['Topics'] ? $result : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException(self::API_GATEWAY_EXCEPTION_MESSAGE . $e->getCode());
        }
    }

    /**
     * Returns a list of the requester's subscriptions.
     * Each call returns a limited list of subscriptions, up to 100.
     * If there are more subscriptions, a NextToken is also returned.
     * Use the NextToken parameter in a new ListSubscriptions call to get further results.
     * This action is throttled at 30 transactions per second (TPS).
     *
     * @return Result|bool
     * @throws SNSPushException
     */
    public function getAllSubscriptions()
    {
        try {
            $result = $this->client->listSubscriptions();
            return $result['Subscriptions'] ? $result : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException(self::API_GATEWAY_EXCEPTION_MESSAGE . $e->getCode());
        }
    }

    /**
     * Lists the endpoints and endpoint attributes for devices in a supported push notification service, such as GCM and APNS.
     * The results for ListEndpointsByPlatformApplication are paginated and return a limited list of endpoints, up to 100.
     * If additional records are available after the first page results, then a NextToken string will be returned.
     * To receive the next page, you call ListEndpointsByPlatformApplication again using the NextToken string received from the previous call.
     * When there are no more records to return, NextToken will be null.
     * This action is throttled at 30 transactions per second (TPS).
     *
     * @param string $platformArn
     *
     * @return Result|bool
     * @throws SNSPushException
     */
    public function getDevicesByPlatformArn(string $platformArn)
    {
        try {
            $result = $this->client->listEndpointsByPlatformApplication(['PlatformApplicationArn' => $platformArn]);
            return $result['Endpoints'] ? $result : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException(self::API_GATEWAY_EXCEPTION_MESSAGE . $e->getCode());
        }
    }

    /**
     * Send push notification to user devices.
     *
     * @param UserDevice[]|Collection $userDevices
     * @param mixed $message
     * @param array  $options
     *
     * @return Result|bool
     * @throws InvalidTypeException
     * @throws UnsupportedPlatformException
     * @throws InvalidArnException
     * @throws InvalidArgumentException
     * @throws SNSPushException
     */
    public function sendPushNotificationToUserDevices($userDevices, $message, $options)
    {
        foreach ($userDevices as $device) { /** @var UserDevice $device */
            $arn = $device->arn_endpoint;
            $arn = $arn instanceof EndpointARN ? $arn : EndpointARN::parse($arn);

            // Retrieve and Call private method "sendPushNotification" from parent class
            $reflection = new \ReflectionObject($this);
            $parentReflection = $reflection->getParentClass();
            $parentSendPushNotification = $parentReflection->getMethod('sendPushNotification');
            $parentSendPushNotification->setAccessible(true);
            $parentSendPushNotification->invoke($this, $arn, $message, $options);
        }
    }
}
