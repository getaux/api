<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Wallet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Wallet')]
class WalletController extends AbstractController
{
    #[Route('/wallet', name: 'api_wallet', methods: ['GET'])]
    #[OA\Get(
        operationId: Wallet::GROUP_GET_WALLET,
        description: 'Get Auction X wallet data',
        summary: 'Get Auction X wallet data',
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content:  new OA\JsonContent(ref: '#/components/schemas/GetWallet')
    )]
    public function ping(Wallet $wallet): Response
    {
        return $this->json($wallet);
    }
}