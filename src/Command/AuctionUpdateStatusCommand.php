<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Asset;
use App\Entity\Auction;
use App\Entity\Bid;
use App\Repository\AuctionRepository;
use App\Service\MessageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:auction:update-status',
    description: 'Update status of ended auctions',
)]
class AuctionUpdateStatusCommand extends Command
{
    private float $percentFees;

    public function __construct(
        private readonly AuctionRepository $auctionRepository,
        private readonly MessageService    $messageService,
        float                              $percentFees
    )
    {
        $this->percentFees = $percentFees;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $endedAuctions = $this->auctionRepository->findEndedAuctions(new \DateTime());

        foreach ($endedAuctions as $auction) {
            if (!$auction->getAsset() instanceof Asset) {
                continue;
            }

            $lastBid = $auction->getLastActiveBid();

            // auction has bid
            if ($lastBid instanceof Bid) {
                // transfer NFT to higher bidder
                $this->messageService->transferNFT(
                    $auction->getAsset()->getInternalId(),
                    $auction->getAsset()->getTokenId(),
                    $auction->getAsset()->getTokenAddress(),
                    $lastBid->getOwner()
                );

                $quantity = intval($lastBid->getQuantity()) * (1 - ($this->percentFees / 100));

                // transfer token to seller
                $this->messageService->transferToken(
                    $auction->getTokenType(),
                    strval($quantity),
                    $lastBid->getDecimals(),
                    $auction->getOwner()
                );

                $auction->setStatus(Auction::STATUS_FILLED);
            } else {
                // event, just refund the seller
                $this->messageService->transferNFT(
                    $auction->getAsset()->getInternalId(),
                    $auction->getAsset()->getTokenId(),
                    $auction->getAsset()->getTokenAddress(),
                    $auction->getOwner()
                );

                $auction->setStatus(Auction::STATUS_EXPIRED);
            }

            // then update auction status
            $this->auctionRepository->add($auction);
        }

        return Command::SUCCESS;
    }
}
