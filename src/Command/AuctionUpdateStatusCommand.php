<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Asset;
use App\Entity\Auction;
use App\Entity\Bid;
use App\Entity\Message;
use App\Repository\AuctionRepository;
use App\Repository\BidRepository;
use App\Service\MessageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:auction:update-status',
    description: 'Update status of ended auctions',
)]
class AuctionUpdateStatusCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly BidRepository     $bidRepository,
        private readonly AuctionRepository $auctionRepository,
        private readonly MessageService    $messageService,
        private readonly float             $percentFees,
        private readonly string            $feesWallet
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->openScheduledAuctions();
        $this->closeEndedAuctions();

        return Command::SUCCESS;
    }

    private function openScheduledAuctions(): void
    {
        $this->addLog('Check scheduled auctions');
        $scheduledAuctions = $this->auctionRepository->findScheduledAuctions(new \DateTime());

        /** @var Auction $scheduledAuction */
        foreach ($scheduledAuctions as $scheduledAuction) {
            $this->addLog(sprintf('Start auction #%s', $scheduledAuction->getId()));

            $scheduledAuction->setStatus(Auction::STATUS_ACTIVE);
            $this->auctionRepository->add($scheduledAuction);
        }
    }

    private function closeEndedAuctions(): void
    {
        $this->addLog('Check ended auctions');
        $endedAuctions = $this->auctionRepository->findEndedAuctions(new \DateTime());

        foreach ($endedAuctions as $auction) {
            $this->addLog(sprintf('End auction #%s', $auction->getId()));

            if (!$auction->getAsset() instanceof Asset) {
                continue;
            }

            $lastBid = $auction->getLastActiveBid();

            // auction has bid
            if ($lastBid instanceof Bid) {
                $this->addLog(sprintf('Auction #%s has bid(s)', $auction->getId()));

                // update status of auction to 'filled'
                $auction->setStatus(Auction::STATUS_FILLED);
                $this->auctionRepository->add($auction);

                // update status of bid to 'won'
                $lastBid->setStatus(Bid::STATUS_WON);
                $this->bidRepository->add($lastBid);

                // transfer NFT to higher bidder
                $this->messageService->transferNFT(
                    Message::TASK_TRANSFER_NFT,
                    $auction->getAsset()->getInternalId(),
                    $auction->getAsset()->getTokenId(),
                    $auction->getAsset()->getCollection()->getAddress(),
                    $lastBid->getOwner(),
                    $auction
                );

                if ($this->percentFees > 0) {
                    $quantityToPay = bcmul($lastBid->getQuantity(), (string)(1 - ($this->percentFees / 100)));
                    $hasFees = true;
                } else {
                    $quantityToPay = $lastBid->getQuantity();
                    $hasFees = false;
                }

                // transfer token to seller
                $this->messageService->transferToken(
                    Message::TASK_PAYMENT,
                    $auction->getTokenType(),
                    $quantityToPay,
                    $lastBid->getDecimals(),
                    $auction->getOwner(),
                    $lastBid
                );

                if ($hasFees) {
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
                }
            } else {
                $this->addLog(sprintf('Auction #%s has no bid', $auction->getId()));

                // even, return NFT to the seller
                $this->messageService->transferNFT(
                    Message::TASK_REFUND_NFT,
                    $auction->getAsset()->getInternalId(),
                    $auction->getAsset()->getTokenId(),
                    $auction->getAsset()->getCollection()->getAddress(),
                    $auction->getOwner(),
                    $auction
                );

                // update status of auction
                $auction->setStatus(Auction::STATUS_EXPIRED);
                $this->auctionRepository->add($auction);
            }
        }
    }

    private function addLog(string $log): void
    {
        $this->io->writeln(sprintf('%s - %s', date('Y-m-d H:i:s'), $log));
    }
}
