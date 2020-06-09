<?php

namespace App\Http\Middleware;

use App\Constants\RouteConstants;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class SanitizationData extends TransformsRequest
{
    protected $except = RouteConstants::REQUEST_FIELDS_NAMES_EXCEPT_SANITIZE;

    protected function transform($key, $value)
    {
        if (\in_array($key, RouteConstants::REQUEST_FIELDS_NAMES_FOR_SANITIZE, true)) {
            $value = mb_strtolower($value);
        }

        return $value;
    }
}
