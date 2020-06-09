<?php

/**
 * Generate the URL to a named route.
 *
 * @param array|string $name
 * @param mixed        $parameters
 * @param string       $version
 * @param bool         $absolute
 *
 * @return null|string
 */
function apiRoute(
    $name,
    array $parameters = [],
    string $version = 'v1',
    bool $absolute = true
): ?string {
    if (!$name) {return '';}

    $urlGenerator = app(Dingo\Api\Routing\UrlGenerator::class)->version($version);
    $scheme = config('api.scheme');

    return 'https' === $scheme
        ? $urlGenerator->secure($urlGenerator->route($name, $parameters, false))
        : $urlGenerator->route($name, $parameters, $absolute)
    ;
}

/**
 * @param $request
 *
 * @return bool
 */
function isApiRequest(\Illuminate\Http\Request $request): bool {
    return $request instanceof \Dingo\Api\Http\Request;
}

/**
 * @return string
 */
function jwtToken(): string {
    return auth()->user() ? \JWTAuth::fromUser(auth()->user()) : '';
}

/**
 * @param string    $string
 * @param bool|null $case TRUE is Uppercase, FALSE is Lowercase, NULL is MixedCase (origin)
 *
 * @return string
 */
function strCase(
    string $string,
    ?bool $case = false
): string {
    switch ($case) {
        case true:
            $string = mb_strtoupper($string);
            break;
        case false:
            $string = mb_strtolower($string);
            break;
    }

    return $string;
}

/**
 * @param string $str
 *
 * @return mixed|string
 */
function sanitizeString (string $str)
{
    $str = strip_tags($str);
    $str = preg_replace('/[\r\n\t ]+/', '-', $str);
    $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', '-', $str);
    $str = strtolower($str);
    $str = html_entity_decode( $str, ENT_QUOTES, 'utf-8' );
    $str = htmlentities($str, ENT_QUOTES, 'utf-8');
    $str = preg_replace('/(&)([a-z])([a-z]+;)/i', '$2', $str);
    $str = str_replace(' ', '-', $str);
    $str = rawurlencode($str);
    $str = str_replace('%', '-', $str);
    $str = preg_replace('/[-]+/', '-', $str);

    return $str;
}

/*
* Description: Generate random numbers sequence of the specified length consisting of digits [0-9].
*
* @param int $length
*
* @return string
*/
function generateRandomNumeric(int $length = 8): string
{
    return generateRandomString($length, null, true);
}

/**
 * Description: Generate random string of the specified length from chars list consisting of letters [A-Za-z] and digits [0-9].
 * @param int       $length
 * @param bool|null $case String register case: true is UpperCase, false is lowerCase, null is MixedCase
 * @param bool      $onlyNumbers
 *
 * @return string
 */
function generateRandomString(
    int $length = 8,
    ?bool $case = false,
    bool $onlyNumbers = false
): string {
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $chars = $onlyNumbers ? $numbers : $letters . $numbers;
    $charsCount = \strlen($chars);
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= substr($chars, random_int(0, $charsCount - 1), 1);
    }

    return strCase($out, $case);
}

/**
 * @param null|string $path
 * @param null|string $locale
 *
 * @return string
 */
function getLangFileContent(
    ?string $path = '',
    ?string $locale = ''
): ?string {
    $locale = $locale ?: app()->getLocale();
    $path = $path ?: base_path('/resources/lang/' . $locale . '.json');
    $content = file_exists($path) ? \File::get($path) : null;

    return $content;
}
