<?php

namespace App\Constants;

/**
 * Class RoleConstants
 *
 * @package App\Constants
 */
class RoleConstants
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_BUSINESS = 'business';
    public const ROLE_WORKER = 'worker';
    public const ROLE_TOURIST = 'tourist';

    public const ROLE_DEFAULT = self::ROLE_TOURIST;

    public const ALL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_BUSINESS,
        self::ROLE_WORKER,
        self::ROLE_TOURIST,
    ];

    public const ALL_ADMIN_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_BUSINESS,
        self::ROLE_WORKER,
    ];

    public const ALL_ADMIN_AREA_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_BUSINESS,
    ];

    public const ALL_PUBLIC_ROLES = [
        self::ROLE_TOURIST,
    ];
}
