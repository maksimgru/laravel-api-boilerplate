<?php

namespace App\Constants;

/**
 * Class UserConstants
 *
 * @package App\Constants
 */
class MediaLibraryConstants
{
    public const COLLECTION_NAME_AVATAR = 'avatar';
    public const REQUEST_FIELD_NAME_AVATAR = 'avatar';

    public const COLLECTION_NAME_MAIN_IMAGE = 'main-image';
    public const REQUEST_FIELD_NAME_MAIN_IMAGE = 'main_image';

    public const COLLECTION_NAME_GALLERY = 'gallery';
    public const REQUEST_FIELD_NAME_GALLERY = 'gallery';

    public const THUMB_SMALL = 'thumb-small';
    public const THUMB_MEDIUM = 'thumb-medium';
    public const THUMB_LARGE = 'thumb-large';
    public const ALL_THUMB_SIZE_ALIASES = [
        self::THUMB_SMALL,
        self::THUMB_MEDIUM,
        self::THUMB_LARGE,
    ];
}
