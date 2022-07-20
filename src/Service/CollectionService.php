<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Collection;

class CollectionService
{
    public function mapTotalAuctions(array $collections, array $auctionsByCollection): void
    {
        /** @var Collection $collection */
        foreach ($collections as $collection) {
            if (isset($auctionsByCollection[$collection->getAddress()])) {
                $collection->setTotalAuctions($auctionsByCollection[$collection->getAddress()]['totalAuctions']);
            }
        }
    }
}
