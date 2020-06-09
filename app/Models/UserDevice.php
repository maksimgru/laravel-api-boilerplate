<?php

namespace App\Models;

use App\Constants\UserDevicesConstants;
use App\Events\UserDeviceDeletedEvent;
use App\Transformers\UserDeviceTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Specialtactics\L5Api\Models\Builder as BuilderL5;

/**
 * Class UserDevice
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $userId
 * @property string $device_type
 * @property string $deviceType
 * @property string $device_token
 * @property string $deviceToken
 * @property string|null $device_id
 * @property string|null $deviceId
 * @property string|null $arn_endpoint
 * @property string|null $arnEndpoint
 * @property string|null $arn_subscriptions
 * @property string|null $arnSubscriptions
 * @property Carbon|null $created_at
 * @property Carbon|null $createdAt
 * @property Carbon|null $updated_at
 * @property Carbon|null $updatedAt
 * @property-read \App\Models\User $user
 * @method static BuilderL5|UserDevice newModelQuery()
 * @method static BuilderL5|UserDevice newQuery()
 * @method static BuilderL5|UserDevice query()
 * @method static Builder|UserDevice whereArnEndpoint($value)
 * @method static Builder|UserDevice whereCreatedAt($value)
 * @method static Builder|UserDevice whereDeviceId($value)
 * @method static Builder|UserDevice whereDeviceToken($value)
 * @method static Builder|UserDevice whereId($value)
 * @method static Builder|UserDevice whereType($value)
 * @method static Builder|UserDevice whereUpdatedAt($value)
 * @method static Builder|UserDevice whereUserId($value)
 * @mixin \Eloquent
 */
class UserDevice extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_devices';

    /**
     * @var array
     */
    public static $itemWith = [
        'user',
    ];

    /**
     * @var array
     */
    public static $collectionWith = [
        'user',
    ];

    /**
     * @var array
     */
    public static $availableWith = [
        'user',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'device_type',
        'device_token',
        'device_id',
        'arn_endpoint',
        'arn_subscriptions',
    ];

    /**
     * @var array
     */
    protected static $selectable = [
        'id',
        'user_id',
        'device_type',
        'device_token',
        'device_id',
        'arn_endpoint',
        'arn_subscriptions',
    ];

    /**
     * @var array
     */
    protected static $searchable = [
        'device_token',
        'device_id',
        'arn_endpoint',
        'arn_subscriptions',
    ];

    /**
     * @var array
     */
    protected static $orderable = [
        'id',
        'user_id',
        'device_type',
        'device_token',
        'device_id',
        'arn_endpoint',
        'arn_subscriptions',

        'users..user_id;users.username',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'arn_subscriptions' => 'json',
    ];

    /**
     * Model's custom transformer
     */
    public static $transformer = UserDeviceTransformer::class;

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param User $user
     *
     * @return Collection
     */
    public static function getByUser(User $user): Collection
    {
        return static::query()
            ->where('user_id', $user->getKey())
            ->whereIn('device_type', UserDevicesConstants::$allowedDeviceTypesIds)
            ->all()
        ;
    }

    /**
     * @param string|null $deviceToken
     *
     * @return int
     */
    public static function removeByDeviceToken(?string $deviceToken)
    {
        return static::query()
            ->where('device_token', $deviceToken)
            ->delete()
        ;
    }

    /**
     * @param string|null $deviceId
     *
     * @return int
     */
    public static function removeByDeviceId(?string $deviceId)
    {
        return static::query()
            ->where('device_id', $deviceId)
            ->delete()
        ;
    }


    /**
     * @return int
     * @throws \Exception
     */
    public static function getDeviceTypeId($deviceType)
    {
        if (isset(UserDevicesConstants::$allowedDeviceTypesMap[$deviceType])) {
            return UserDevicesConstants::$allowedDeviceTypesMap[$deviceType];
        } else {
            throw new \Exception(
                sprintf(
                    'The Device with type "%1$s" is not found!',
                    $deviceType
                )
            );
        }
    }

    /**
     * @param int $deviceTypeId
     *
     * @return string
     */
    public static function getDevicePlatformName(int $deviceTypeId): string
    {
        $platformName = 'default';
        if (static::isIosPlatform($deviceTypeId)) {
            $platformName = UserDevicesConstants::IOS_DEVICE_TYPE;
        } elseif (static::isAndroidPlatform($deviceTypeId)) {
            $platformName = UserDevicesConstants::ANDROID_DEVICE_TYPE;
        }

        return $platformName;
    }

    /**
     * @param int|string $deviceType
     *
     * @return bool
     */
    public static function isIosPlatform($deviceType): bool
    {
        return \in_array($deviceType, [UserDevicesConstants::IOS_DEVICE_TYPE, UserDevicesConstants::IOS_DEVICE_TYPE_ID]);
    }

    /**
     * @param int|string $deviceType
     *
     * @return bool
     */
    public static function isAndroidPlatform($deviceType): bool
    {
        return \in_array($deviceType, [UserDevicesConstants::ANDROID_DEVICE_TYPE, UserDevicesConstants::ANDROID_DEVICE_TYPE_ID]);
    }
}
