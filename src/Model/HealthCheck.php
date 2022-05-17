<?php

declare(strict_types=1);

namespace App\Model;

use OpenApi\Attributes as OA;

class HealthCheck
{
    #[OA\Property(
        description: 'Health status',
        type: 'string'
    )]
    public string $result = 'OK';

    #[OA\Property(
        description: 'Timestamp of the response',
        type: 'string',
        format: 'date-time'
    )]
    public string $timestamp;

    public function __construct()
    {
        $this->timestamp = (new \DateTime)->format(\DateTimeInterface::ATOM);
    }
}