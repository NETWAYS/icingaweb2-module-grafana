<?php

namespace Icinga\Module\Grafana\Helpers;

use Icinga\User;

class Util
{
    public static function graphiteReplace(string $string = ''): string
    {
        $string = preg_replace('/[^a-zA-Z0-9\*\-:]/', '_', $string);

        return $string;
    }

    public static function taosReplace(string $string = ''): string
    {
        $string = preg_replace('/[^a-zA-Z0-9\*\-:]/', '_', $string);

        return $string;
    }

    /**
     * getUserThemeMode returns the users configured Theme Mode.
     * Since we cannot handle the 'system' setting (it's client-side),
     * we default to the mode given. This is used to pass the default
     * from the module's configuration.
     *
     * @param User $user
     * @return string
     */
    public static function getUserThemeMode(User $user, string $defaultMode = 'dark'): string
    {
        $mode = $defaultMode;

        if (isset($user)) {
            $mode = $user->getPreferences()->getValue('icingaweb', 'theme_mode', $mode);
        }

        // Could be system, which we cannot handle since it's browser-side
        if (!in_array($mode, ['dark', 'light'])) {
            $mode = $defaultMode;
        }

        return $mode;
    }

    /**
     * httpStatusCodeToString translates a HTTP status code to a readable message
     *
     * @param int $code HTTP status code
     * @return string
     */
    public static function httpStatusCodeToString(int $code = 0): string
    {
        $statuscodes = [
            '100' => 'Continue',
            '101' => 'Switching Protocols',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Timeout',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Requested Range Not Satisfiable',
            '417' => 'Expectation Failed',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
            '505' => 'HTTP Version Not Supported'
        ];
        $code = (string)$code;
        if (array_key_exists($code, $statuscodes)) {
            return $statuscodes[$code];
        }
        return $code;
    }
}
