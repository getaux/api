<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ImmutableXClient;
use App\Entity\Asset;
use App\Entity\Auction;
use App\Entity\Bid;
use App\Helper\TokenHelper;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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

    public function checkAuctionDeposit(Auction $auction): void
    {
        $transfer = $this->immutableXClient->get(sprintf('v1/transfers/%s', $auction->getTransferId()));

        // check if receiver is the escrow wallet
        if (strtolower($transfer['receiver']) !== strtolower($this->escrowWallet)) {
            throw new \Exception(sprintf('Receiver of transfer %s is invalid', $auction->getTransferId()), 400);
        }

        $asset = $this->assetRepository->findOneBy([
            'tokenAddress' => $transfer['token']['data']['token_address'],
            'tokenId' => $transfer['token']['data']['token_id'],
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

    public function updateAsset(string $tokenAddress, string $internalId, Asset $asset): Asset
    {
        $apiAssetResult = $this->immutableXClient->get(
            sprintf('v1/assets/%s/%s', $tokenAddress, $internalId)
        );

        $asset->setInternalId($apiAssetResult['id']);
        $asset->setTokenId($apiAssetResult['token_id']);
        $asset->setImageUrl($apiAssetResult['image_url']);
        $asset->setName((string)$apiAssetResult['name']);
        $asset->setTokenAddress($apiAssetResult['token_address']);
        $asset->setInternalId($apiAssetResult['token_id']);

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }

    public function checkBidDeposit(Bid $bid, Auction $auction): void
    {
        $transfer = $this->immutableXClient->get(sprintf('v1/transfers/%s', $bid->getTransferId()));

        // check if receiver is the escrow wallet
        if (strtolower($transfer['receiver']) !== strtolower($this->escrowWallet)) {
            throw new \Exception(sprintf('Receiver of transfer %s is invalid', $bid->getTransferId()), 400);
        }

        // check if bid currency is the same as auction
        $token = TokenHelper::getTokenFromIMXTransfer($transfer);

        if ($token !== $auction->getTokenType()) {
            throw new BadRequestException(
                sprintf('Invalid bid currency: %s sent, %s excepted', $token, $auction->getTokenType())
            );
        }

        $bid->setQuantity($transfer['token']['data']['quantity']);
        $bid->setDecimals($transfer['token']['data']['decimals']);

        $bid->setOwner($transfer['user']);
        $bid->setCreatedAt(new \DateTimeImmutable($transfer['timestamp']));
    }
}
