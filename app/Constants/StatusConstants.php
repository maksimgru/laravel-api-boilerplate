<?php

namespace App\Constants;

/**
 * Class StatusConstants
 *
 * @package App\Constants
 */
class StatusConstants
{
    public const STATUS_INPROGRESS = 'in-progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const ALL_STATUSES = [
        self::STATUS_INPROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
    ];
}
