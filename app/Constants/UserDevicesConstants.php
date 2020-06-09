<?php

namespace App\Constants;

/**
 * Class UserDevicesConstants
 *
 * @package App\Constants
 */
class UserDevicesConstants
{
    public const IOS_DEVICE_TYPE = 'ios';
    public const ANDROID_DEVICE_TYPE = 'android';

    public const IOS_DEVICE_TYPE_ID = 1;
    public const ANDROID_DEVICE_TYPE_ID = 2;

    public static $allowedDeviceTypes = [
        self::IOS_DEVICE_TYPE,
        self::ANDROID_DEVICE_TYPE,
    ];

    public static $allowedDeviceTypesIds = [
        self::IOS_DEVICE_TYPE_ID,
        self::ANDROID_DEVICE_TYPE_ID,
    ];

    public static $allowedDeviceTypesMap = [
        self::IOS_DEVICE_TYPE     => self::IOS_DEVICE_TYPE_ID,
        self::ANDROID_DEVICE_TYPE => self::ANDROID_DEVICE_TYPE_ID,
    ];
}
