<?php

declare(strict_types=1);

namespace App\Helper;

class TokenHelper
{
    public const TOKENS = [
        'ETH',
        'APE',
        'GODS',
        'GOG',
        'IMX',
        'OMI',
        'USDC',
        'VCO',
        'VCORE',
    ];

    public const ERC20_TOKENS = [
        '0x4d224452801aced8b2f0aebe155379bb5d594381' => 'APE',
        '0xccc8cb5229b0ac8069c51fd58367fd1e622afd97' => 'GODS',
        '0x9ab7bb7fdc60f4357ecfef43986818a2a3569c62' => 'GOG',
        '0xf57e7e7c23978c3caec3c3548e3d615c346e79ff' => 'IMX',
        '0xed35af169af46a02ee13b9d79eb57d6d68c1749e' => 'OMI',
        '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48' => 'USDC',
        '0x2caa4021e580b07d92adf8a40ec53b33a215d620' => 'VCO',
        '0x733b5056a0697e7a4357305fe452999a0c409feb' => 'VCORE',
    ];

    public static function getTokenFromContractAddress(string $contract): string
    {
        return self::ERC20_TOKENS[$contract] ?? $contract;
    }

    public static function getTokenFromIMXTransfer(array $imxTransfer): string
    {
        if ($imxTransfer['token']['type'] === 'ETH') {
            return 'ETH';
        }

        return self::getTokenFromContractAddress($imxTransfer['token']['data']['token_address']);
    }
}
