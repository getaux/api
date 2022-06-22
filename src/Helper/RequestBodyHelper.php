<?php

declare(strict_types=1);

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

class RequestBodyHelper
{
    public static function map(Request $request): array
    {
        return (array)json_decode((string)$request->getContent(), true);
    }
}
