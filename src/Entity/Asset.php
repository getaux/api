<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AssetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[OA\Schema(description: 'Asset linked to auction(s)')]
class Asset
{
    public const GROUP_GET_ASSETS = 'get-assets';
    public const GROUP_GET_ASSET_WITH_AUCTIONS = 'get-asset-with-auctions';
    public const GROUP_GET_ASSET = 'get-asset';
    public const GROUP_UPDATE_ASSET = 'update-asset';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([Auction::GROUP_GET_AUCTION, self::GROUP_GET_ASSET])]
    #[OA\Property(description: 'AuctionX internal ID of the asset', format: 'int')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([Auction::GROUP_GET_AUCTION, self::GROUP_GET_ASSET])]
    #[OA\Property(description: 'Internal Immutable X Internal ID')]
    private string $internalId = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([Auction::GROUP_GET_AUCTION, self::GROUP_GET_ASSET])]
    #[OA\Property(description: 'Address of the ERC721 contract')]
    private string $tokenAddress = '';

    #[ORM\OneToMany(mappedBy: 'asset', targetEntity: Auction::class)]
    #[Groups([self::GROUP_GET_ASSET_WITH_AUCTIONS])]
    #[OA\Property(
        description: 'Auctions related to the asset',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/Auction.list')
    )]
    private Collection $auctions;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([Auction::GROUP_GET_AUCTION, self::GROUP_GET_ASSET])]
    #[OA\Property(description: 'URL of the image which should be used for this asset')]
    private string $imageUrl;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([Auction::GROUP_GET_AUCTION, self::GROUP_GET_ASSET])]
    #[OA\Property(description: 'Name of this asset')]
    private string $name;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime_immutable')]
    #[OA\Property(
        description: 'Created timestamp of this asset',
        type: 'string',
        format: 'datetime',
    )]
    protected \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([Auction::GROUP_GET_AUCTION, self::GROUP_GET_ASSET])]
    #[OA\Property(description: 'Internal Immutable X Internal ID')]
    private string $tokenId = '';

    public function __construct()
    {
        $this->auctions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInternalId(): string
    {
        return $this->internalId;
    }

    public function setInternalId(string $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getTokenAddress(): string
    {
        return $this->tokenAddress;
    }

    public function setTokenAddress(string $tokenAddress): self
    {
        $this->tokenAddress = $tokenAddress;

        return $this;
    }

    /**
     * @return Collection<int, Auction>
     */
    public function getAuctions(): Collection
    {
        return $this->auctions;
    }

    public function addAuction(Auction $auction): self
    {
        if (!$this->auctions->contains($auction)) {
            $this->auctions[] = $auction;
            $auction->setAsset($this);
        }

        return $this;
    }

    public function removeAuction(Auction $auction): self
    {
        if ($this->auctions->removeElement($auction)) {
            // set the owning side to null (unless already changed)
            if ($auction->getAsset() === $this) {
                $auction->setAsset(null);
            }
        }

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    public function setTokenId(string $tokenId): self
    {
        $this->tokenId = $tokenId;

        return $this;
    }
}
