<?php

namespace Icinga\Module\Grafana\Helpers;

class Util
{
    public static function graphiteReplace(string $string = ''): string
    {
        $string = preg_replace('/[^a-zA-Z0-9\*\-:]/', '_', $string);

        return $string;
    }
}
