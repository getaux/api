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

            $minQuantity = pow(10, rand(16, 18));
            $maxQuantity = $minQuantity * 2;

            $auction->setQuantity((string)rand($minQuantity, $maxQuantity));
            $auction->setDecimals(18);

            $auction->setType(Auction::TYPE[rand(0, count(Auction::TYPE) - 1)]);
            $auction->setStatus(Auction::STATUS[rand(0, count(Auction::STATUS) - 1)]);

            $auction->setTokenType(TokenHelper::TOKENS[rand(0, count(TokenHelper::TOKENS) - 1)]);

            $auction->setTransferId((string)rand(100000000, 999999999));

            $auction->setEndAt(new \DateTime('+1 week'));

            $manager->persist($auction);
        }

        $manager->flush();
    }
}
