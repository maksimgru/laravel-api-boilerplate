<?php

namespace App\Transformers;

use App\Constants\MediaLibraryConstants;
use App\Models\User;

class UserTransformer extends BaseTransformer
{
    /**
     * @param User $user
     *
     * @return array
     * @throws \Exception
     */
    public function transform($user): array
    {
        /** @var User $user */
        $transformed = parent::transform($user);
        $transformed['media']['avatar_urls'] = $user->getFirstMediaThumbsUrls(MediaLibraryConstants::COLLECTION_NAME_AVATAR);
        $transformed['primary_role'] = $user->primaryRole;

        return $transformed;
    }
}
