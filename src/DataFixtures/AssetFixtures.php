<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Client\ImmutableXClient;
use App\Entity\Asset;
use App\Entity\Collection;
use App\Repository\CollectionRepository;
use App\Service\ImmutableService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AssetFixtures extends Fixture
{
    public function __construct(
        private readonly ImmutableXClient     $immutableXClient,
        private readonly CollectionRepository $collectionRepository,
        private readonly ImmutableService     $immutableService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $collectionAddress = '0x9f6ceedacc84e8266c3e7ce6f7bcbf7d1de39501';

        $collectionEntity = $this->collectionRepository->findOneBy([
            'address' => $collectionAddress,
        ]);

        if (!$collectionEntity instanceof Collection) {
            $collectionEntity = $this->immutableService->updateCollection($collectionAddress, new Collection());
        }

        // fetch Highrise Creature Club first 10 assets
        $realAssets = $this->immutableXClient->get('v1/assets', [
            'collection' => $collectionAddress,
            'page_size' => 10
        ]);

        foreach ($realAssets['result'] as $realAsset) {
            $asset = new Asset();
            $asset->setName($realAsset['name']);
            $asset->setTokenId($realAsset['token_id']);
            $asset->setInternalId($realAsset['id']);
            $asset->setImageUrl($realAsset['image_url']);
            $asset->setCollection($collectionEntity);

            $manager->persist($asset);
        }

        $manager->flush();
    }
}
