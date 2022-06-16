<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Asset;
use App\Entity\Auction;
use App\Helper\TokenHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AuctionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $assetRepository = $manager->getRepository(Asset::class);
        $assets = $assetRepository->findAll();

        foreach ($assets as $asset) {
            $auction = new Auction;
            $auction->setAsset($asset);

            $randQuantity = rand(10, 100);

            $auction->setQuantity((string)($randQuantity * pow(10, 16)));
            $auction->setDecimals(18);

            $auction->setType(Auction::TYPES[rand(0, count(Auction::TYPES) - 1)]);
            $auction->setStatus(Auction::STATUS[rand(0, count(Auction::STATUS) - 1)]);
            $auction->setOwner('0xfd3268ce649945293a278c2f0dbd0849faa2d497');

            $auction->setTokenType(TokenHelper::TOKENS[rand(0, count(TokenHelper::TOKENS) - 1)]);

            $auction->setTransferId((string)rand(100000000, 999999999));

            $dateTime = new \DateTime('+1 week');
            $dateFormat = $dateTime->format('Y-m-d H:i:00');
            $dateImmutable = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateFormat);

            if ($dateImmutable) {
                $auction->setEndAt($dateImmutable);
            }

            $manager->persist($auction);
        }

        $manager->flush();
    }
}
