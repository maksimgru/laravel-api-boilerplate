<?php

namespace App\Transformers;

use App\Constants\MediaLibraryConstants;
use App\Models\Page;

class PageTransformer extends BaseTransformer
{
    /**
     * @param Page $page
     *
     * @return array
     * @throws \Exception
     */
    public function transform($page): array
    {
        /** @var Page $page */
        $transformed = parent::transform($page);
        $transformed['media']['main_image'] = $page->getFirstMediaThumbsUrls(MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE);
        $transformed['media']['gallery'] = $page->getMediaThumbsUrls(MediaLibraryConstants::COLLECTION_NAME_GALLERY);

        return $transformed;
    }
}
