<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Asset;
use App\Entity\Auction;
use App\Entity\Bid;
use App\Entity\Message;
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
    public function __construct(
        private readonly AuctionRepository $auctionRepository,
        private readonly MessageService    $messageService,
        private readonly float             $percentFees,
        private readonly string            $feesWallet
    ) {
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

                // update status of auction
                $auction->setStatus(Auction::STATUS_FILLED);
                $this->auctionRepository->add($auction);

                // transfer NFT to higher bidder
                $this->messageService->transferNFT(
                    Message::TASK_TRANSFER_NFT,
                    $auction->getAsset()->getInternalId(),
                    $auction->getAsset()->getTokenId(),
                    $auction->getAsset()->getTokenAddress(),
                    $lastBid->getOwner(),
                    $auction
                );

                $quantityToPay = bcmul($lastBid->getQuantity(), (string)(1 - ($this->percentFees / 100)));

                // transfer token to seller
                $this->messageService->transferToken(
                    Message::TASK_PAYMENT,
                    $auction->getTokenType(),
                    $quantityToPay,
                    $lastBid->getDecimals(),
                    $auction->getOwner(),
                    $lastBid
                );

                $quantityFees = bcsub($lastBid->getQuantity(), $quantityToPay);

                // transfer fees to wallet
                $this->messageService->transferToken(
                    Message::TASK_PAYMENT_FEES,
                    $auction->getTokenType(),
                    $quantityFees,
                    $lastBid->getDecimals(),
                    $this->feesWallet,
                    $lastBid
                );
            } else {

                // update status of auction
                $auction->setStatus(Auction::STATUS_EXPIRED);
                $this->auctionRepository->add($auction);

                // even, return NFT to the seller
                $this->messageService->transferNFT(
                    Message::TASK_REFUND_NFT,
                    $auction->getAsset()->getInternalId(),
                    $auction->getAsset()->getTokenId(),
                    $auction->getAsset()->getTokenAddress(),
                    $auction->getOwner(),
                    $auction
                );
            }
        }

        return Command::SUCCESS;
    }
}
