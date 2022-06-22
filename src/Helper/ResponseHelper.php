<?php

declare(strict_types=1);

namespace App\Helper;

class ResponseHelper
{
    public static function getFirstError(string $string): string
    {
        $xpl = explode("\n", $string);
        return str_replace('ERROR: ', '', $xpl[0]);
    }
}
