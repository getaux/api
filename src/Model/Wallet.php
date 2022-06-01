<?php

declare(strict_types=1);

namespace App\Model;

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;

#[OA\Schema(description: 'Official wallet of AuctionX engine')]
class Wallet
{
    public const GROUP_GET_WALLET = 'get-wallet';

    #[OA\Property(description: 'Public key')]
    #[Groups([self::GROUP_GET_WALLET])]
    public string $publicKey;

    #[OA\Property(description: 'ETH Network')]
    #[Groups([self::GROUP_GET_WALLET])]
    public string $network;

    public function __construct(string $publicKey, string $network)
    {
        $this->publicKey = strtolower($publicKey);
        $this->network = $network === 'dev' ? 'ropsten' : 'mainnet';
    }
}