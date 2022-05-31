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

        $asset = $this->assetRepository->findOneBy([
            'tokenAddress' => $transfer['token']['data']['token_address'],
            'internalId' => $transfer['token']['data']['token_id'],
        ]);

        // then fetch asset data
        $assetEntity = $this->updateAsset(
            $transfer['token']['data']['token_address'],
            $transfer['token']['data']['token_id'],
            $asset ?? new Asset()
        );

        $auction->setAsset($assetEntity);
        $auction->setOwner($transfer['user']);
    }

    public function updateAsset(string $tokenAddress, string $id, Asset $asset): Asset
    {
        $apiAssetResult = $this->immutableXClient->get(
            sprintf('v1/assets/%s/%s', $tokenAddress, $id)
        );

        $asset->setInternalId($apiAssetResult['id']);
        $asset->setImageUrl($apiAssetResult['image_url']);
        $asset->setName($apiAssetResult['name']);
        $asset->setTokenAddress($apiAssetResult['token_address']);
        $asset->setInternalId($apiAssetResult['token_id']);

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }
}