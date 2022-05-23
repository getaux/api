<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ImmutableXClient;
use App\Entity\Asset;
use App\Entity\Auction;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImmutableService
{
    public function __construct(
        private readonly ImmutableXClient       $immutableXClient,
        private readonly AssetRepository        $assetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly string                 $escrowWallet
    )
    {
    }

    public function checkDeposit(Auction $auction): void
    {
        $transfer = $this->immutableXClient->get(sprintf('v1/transfers/%s', $auction->getTransferId()));

        // check if receiver is the escrow wallet
        if (strtolower($transfer['receiver']) !== strtolower($this->escrowWallet)) {
            throw new \Exception(sprintf('Receiver of transfer %s is invalid', $auction->getTransferId()), 400);
        }

        // then fetch asset data
        $asset = $this->importAsset(
            $transfer['token']['data']['token_address'],
            $transfer['token']['data']['token_id']
        );

        $auction->setAsset($asset);
    }

    public function importAsset(string $tokenAddress, string $id): Asset
    {
        $assetEntity = $this->assetRepository->findOneBy([
            'tokenAddress' => $tokenAddress,
            'internalId' => $id,
        ]);

        if (!$assetEntity instanceof Asset) {
            $asset = $this->immutableXClient->get(
                sprintf('v1/assets/%s/%s', $tokenAddress, $id)
            );

            $assetEntity = new Asset;

            $assetEntity->setInternalId($asset['id']);
            $assetEntity->setImageUrl($asset['image_url']);
            $assetEntity->setName($asset['name']);
            $assetEntity->setTokenAddress($asset['token_address']);
            $assetEntity->setInternalId($asset['token_id']);

            $this->entityManager->persist($assetEntity);
            $this->entityManager->flush();
        }

        return $assetEntity;
    }
}