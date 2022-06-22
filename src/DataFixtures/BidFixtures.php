<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Auction;
use App\Entity\Bid;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BidFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $auctionRepository = $manager->getRepository(Auction::class);
        $auctions = $auctionRepository->findAll();

        $transferId = 12345678;

        foreach ($auctions as $auction) {
            $maxBids = rand(2, 5);
            for ($i = 1; $i <= $maxBids; $i++) {
                $bid = new Bid();
                $bid->setAuction($auction);
                $bid->setQuantity((string)($i * pow(10, 18)));
                $bid->setTransferId((string)$transferId);
                $bid->setDecimals(18);
                $bid->setOwner('0xb8f6577961ff927c70d26ac7b691474e5a8e2927');

                if ($auction->getStatus() === Auction::STATUS_CANCELLED ||
                    $auction->getStatus() === Auction::STATUS_EXPIRED
                ) {
                    $status = Bid::STATUS_CANCELLED;
                } elseif ($maxBids === $i) {
                    $status = Bid::STATUS_ACTIVE;
                } else {
                    $status = Bid::STATUS_OVERPAID;
                }

                $bid->setStatus($status);

                $manager->persist($bid);

                $transferId++;
            }
        }

        $manager->flush();
    }
}
