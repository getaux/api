<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Client\ImmutableXClient;
use App\Entity\Asset;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AssetFixtures extends Fixture
{
    public function __construct(private readonly ImmutableXClient $immutableXClient)
    {
        // for fixtures, we only get real data
        $this->immutableXClient->setEnvironment(ImmutableXClient::ENV_PROD);
    }

    public function load(ObjectManager $manager): void
    {
        // fetch Highrise Creature Club first 10 assets
        $realAssets = $this->immutableXClient->get('v1/assets', [
            'collection' => '0xb0e827c9ab5e68d243f707f832b756981987f704',
            'page_size' => 10
        ]);

        foreach ($realAssets['result'] as $realAsset) {
            $asset = new Asset;
            $asset->setName($realAsset['name']);
            $asset->setTokenId($realAsset['token_id']);
            $asset->setInternalId($realAsset['id']);
            $asset->setTokenAddress($realAsset['token_address']);
            $asset->setImageUrl($realAsset['image_url']);

            $manager->persist($asset);
        }

        $manager->flush();
    }
}
