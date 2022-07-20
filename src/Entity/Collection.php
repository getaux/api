<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: CollectionRepository::class)]
class Collection
{
    public const GROUP_GET_COLLECTIONS = 'get-collections';
    public const GROUP_GET_COLLECTION = 'get-collection';
    public const GROUP_UPDATE_COLLECTION = 'update-collection';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups([Asset::GROUP_GET_ASSET, self::GROUP_GET_COLLECTION])]
    #[OA\Property(description: 'AuctionX internal ID of the collection', format: 'int')]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Asset::GROUP_GET_ASSET, self::GROUP_GET_COLLECTION])]
    #[OA\Property(description: 'Collection contract address')]
    private string $address;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Asset::GROUP_GET_ASSET, self::GROUP_GET_COLLECTION])]
    #[OA\Property(description: 'Name of the collection')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([Asset::GROUP_GET_ASSET, self::GROUP_GET_COLLECTION])]
    #[OA\Property(description: 'Image URL of the collection')]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'collection', targetEntity: Asset::class, orphanRemoval: true)]
    private DoctrineCollection $assets;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([Asset::GROUP_GET_ASSET, self::GROUP_GET_COLLECTION])]
    #[OA\Property(description: 'Description of the collection')]
    private ?string $description = null;

    #[Groups([Asset::GROUP_GET_ASSET, self::GROUP_GET_COLLECTION])]
    #[OA\Property(description: 'Number of auctions for the collection', format: 'int', example: 1)]
    private int $totalAuctions = 0;

    public function __construct()
    {
        $this->assets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return DoctrineCollection<int, Asset>
     */
    public function getAssets(): DoctrineCollection
    {
        return $this->assets;
    }

    public function addAsset(Asset $asset): self
    {
        if (!$this->assets->contains($asset)) {
            $this->assets[] = $asset;
            $asset->setCollection($this);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTotalAuctions(): int
    {
        return $this->totalAuctions;
    }

    public function setTotalAuctions(int $totalAuctions): void
    {
        $this->totalAuctions = $totalAuctions;
    }
}
