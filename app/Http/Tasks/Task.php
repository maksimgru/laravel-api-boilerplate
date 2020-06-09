<?php

namespace App\Http\Tasks;

use App\Constants\RouteConstants;

/**
 * Class Task
 *
 * @package App\Http\Tasks
 */
class Task
{
    /**
     * @param array|null $data
     *
     * @return int
     */
    protected function getItemsPerPage(?array $data = []): int {
        $perPage = empty($data[RouteConstants::REQUEST_FIELD_NAME_PER_PAGE])
            ? null
            : (int) $data[RouteConstants::REQUEST_FIELD_NAME_PER_PAGE]
        ;

        return $perPage ?: request()->route()->getController()->getItemsPerPage();
    }
}
