<?php

declare(strict_types=1);

namespace App\Helper;

class StringHelper
{
    public static function sanitize(string $text): string
    {
        return (string)preg_replace('/[^a-zA-Z0-9_ -]/s', '', $text);
    }
}
