<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\ImmutableXClient;
use App\Entity\Asset;
use App\Entity\Auction;
use App\Entity\Bid;
use App\Entity\Collection;
use App\Entity\Message;
use App\Helper\TokenHelper;
use App\Repository\AssetRepository;
use App\Repository\BidRepository;
use App\Repository\CollectionRepository;
use App\Service\Exception\BadBidException;
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
        private readonly CollectionRepository   $collectionRepository,
    ) {
    }

    public function checkAuctionDeposit(Auction $auction): void
    {
        $transfer = $this->immutableXClient->get(sprintf('v1/transfers/%s', $auction->getTransferId()));

        // check if receiver is the escrow wallet
        if (strtolower($transfer['receiver']) !== strtolower($this->escrowWallet)) {
            throw new \Exception(sprintf('Receiver of transfer %s is invalid', $auction->getTransferId()), 400);
        }

        $collection = $this->collectionRepository->findOneBy([
            'address' => $transfer['token']['data']['token_address'],
        ]);

        if (!$collection instanceof Collection) {
            $collection = $this->updateCollection($transfer['token']['data']['token_address'], new Collection());
        }

        $asset = $this->assetRepository->findOneBy([
            'collection' => $collection,
            'tokenId' => $transfer['token']['data']['token_id'],
        ]);

        // then fetch asset data
        $assetEntity = $this->updateAsset(
            $collection,
            $transfer['token']['data']['token_id'],
            $asset ?? new Asset()
        );

        $auction->setAsset($assetEntity);
        $auction->setOwner($transfer['user']);
    }

    public function updateAsset(Collection $collection, string $internalId, Asset $asset): Asset
    {
        $apiAssetResult = $this->immutableXClient->get(
            sprintf('v1/assets/%s/%s', $collection->getAddress(), $internalId)
        );

        if ($asset->getInternalId() === '') {
            $asset->setCollection($collection);
        }

        $asset->setInternalId($apiAssetResult['id']);
        $asset->setTokenId($apiAssetResult['token_id']);
        $asset->setImageUrl($apiAssetResult['image_url']);
        $asset->setName((string)$apiAssetResult['name']);
        $asset->setInternalId($apiAssetResult['token_id']);

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }

    public function updateCollection(string $collectionAddress, Collection $collection): Collection
    {
        $apiCollectionResult = $this->immutableXClient->get(
            sprintf('v1/collections/%s', $collectionAddress)
        );

        $collection->setName($apiCollectionResult['name']);
        $collection->setAddress($apiCollectionResult['address']);
        $collection->setDescription($apiCollectionResult['description']);
        $collection->setImage($apiCollectionResult['collection_image_url']);

        $this->entityManager->persist($collection);
        $this->entityManager->flush();

        return $collection;
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
            throw new BadBidException(sprintf('Receiver of transfer %s is invalid', $bid->getTransferId()));
        }

        // check if bid currency is the same as auction
        $token = TokenHelper::getTokenFromIMXTransfer($transfer);

        if ($token !== $auction->getTokenType()) {
            // we refund bid
            $this->messageService->transferToken(
                Message::TASK_REFUND_BID,
                $token,
                $transfer['token']['data']['quantity'],
                $transfer['token']['data']['decimals'],
                $transfer['user'],
                $bid
            );
            throw new BadBidException(
                sprintf('Invalid bid currency: %s sent, %s excepted. Refund in progress.', $token, $auction->getTokenType())
            );
        }

        // check if bid quantity is superior to auction minimum price
        if ($transfer['token']['data']['quantity'] < $auction->getQuantity()) {
            // we refund bid
            $this->messageService->transferToken(
                Message::TASK_REFUND_BID,
                $token,
                $transfer['token']['data']['quantity'],
                $transfer['token']['data']['decimals'],
                $transfer['user'],
                $bid
            );
            throw new BadBidException('Bid should be superior to auction price. Refund in progress.');
        }

        // fetch previous bid
        $previousBid = $auction->getLastBid();

        if ($previousBid instanceof Bid) {
            // check if the new bid is higher than previous one
            if ($bid->getQuantity() <= $previousBid->getQuantity()) {
                // we have to refund bid
                $this->messageService->transferToken(
                    Message::TASK_REFUND_BID,
                    $auction->getTokenType(),
                    $bid->getQuantity(),
                    $bid->getDecimals(),
                    $bid->getOwner(),
                    $bid
                );

                throw new BadBidException('Bid should be superior to previous bid. Refund in progress.');
            }

            $previousBid->setStatus(Bid::STATUS_OVERPAID);
            $this->bidRepository->add($previousBid);

            // we refund previous bid
            $this->messageService->transferToken(
                Message::TASK_REFUND_BID,
                $auction->getTokenType(),
                $previousBid->getQuantity(),
                $previousBid->getDecimals(),
                $previousBid->getOwner(),
                $previousBid
            );
        }
    }
}
