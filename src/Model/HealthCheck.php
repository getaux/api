<?php

declare(strict_types=1);

namespace App\Model;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;

#[OA\Schema(description: 'Health check of AuctionX engine')]
class HealthCheck
{
    public const GROUP_GET_HEALTH_CHECK = 'get-health-check';

    #[OA\Property(description: 'Health status')]
    #[Groups([self::GROUP_GET_HEALTH_CHECK])]
    public string $result = 'OK';

    #[OA\Property(description: 'Timestamp of the response', format: 'datetime')]
    #[Groups([self::GROUP_GET_HEALTH_CHECK])]
    public string $timestamp;

    public function __construct()
    {
        $this->timestamp = (new \DateTime())->format(\DateTimeInterface::ATOM);
    }
}
