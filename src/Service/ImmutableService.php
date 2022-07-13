<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ImmutableXClient;
use App\Entity\Asset;
use App\Entity\Auction;
use App\Entity\Bid;
use App\Helper\TokenHelper;
use App\Repository\AssetRepository;
use App\Repository\BidRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImmutableService
{
    public function __construct(
        private readonly ImmutableXClient       $immutableXClient,
        private readonly AssetRepository        $assetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly string                 $escrowWallet,
        private readonly MessageService         $messageService,
        private readonly BidRepository          $bidRepository,
    ) {
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
        $transfer = $this->immutableXClient->get(sprintf('v1/transfers/%s', $bid->getTransferId()), [], false);

        // update bid with api response
        $bid->setQuantity($transfer['token']['data']['quantity']);
        $bid->setDecimals($transfer['token']['data']['decimals']);

        $bid->setOwner($transfer['user']);
        $bid->setCreatedAt(new \DateTimeImmutable($transfer['timestamp']));

        // check if receiver is the escrow wallet
        if (strtolower($transfer['receiver']) !== strtolower($this->escrowWallet)) {
            throw new \Exception(sprintf('Receiver of transfer %s is invalid', $bid->getTransferId()), 400);
        }

        // check if bid currency is the same as auction
        $token = TokenHelper::getTokenFromIMXTransfer($transfer);

        if ($token !== $auction->getTokenType()) {
            // we refund bid
            $this->messageService->transferToken(
                $token,
                $transfer['token']['data']['quantity'],
                $transfer['token']['data']['decimals'],
                $transfer['user']
            );
            throw new \Exception(
                sprintf('Invalid bid currency: %s sent, %s excepted. Refund in progress.', $token, $auction->getTokenType())
            );
        }

        // check if bid quantity is superior to auction minimum price
        if ($transfer['token']['data']['quantity'] < $auction->getQuantity()) {
            // we refund bid
            $this->messageService->transferToken(
                $token,
                $transfer['token']['data']['quantity'],
                $transfer['token']['data']['decimals'],
                $transfer['user']
            );
            throw new \Exception('Bid should be superior to auction price. Refund in progress.');
        }

        // fetch previous bid
        $previousBid = $auction->getLastBid();

        if ($previousBid instanceof Bid) {
            // check if the new bid is higher than previous one
            if ($bid->getQuantity() <= $previousBid->getQuantity()) {
                // we have to refund bid
                $this->messageService->transferToken(
                    $auction->getTokenType(),
                    $bid->getQuantity(),
                    $bid->getDecimals(),
                    $bid->getOwner()
                );

                throw new \Exception('Bid should be superior to previous bid. Refund in progress.');
            }

            $previousBid->setStatus(Bid::STATUS_OVERPAID);
            $this->bidRepository->add($previousBid);

            // we refund previous bid
            $this->messageService->transferToken(
                $auction->getTokenType(),
                $previousBid->getQuantity(),
                $previousBid->getDecimals(),
                $previousBid->getOwner()
            );
        }
    }
}
