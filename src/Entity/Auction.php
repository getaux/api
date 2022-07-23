<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\TokenHelper;
use App\Repository\AuctionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: AuctionRepository::class)]
#[UniqueEntity('transferId')]
#[OA\Schema(description: 'Auction linked to an asset', required: [
    'type', 'status', 'transferId', 'quantity', 'decimals', 'tokenType', 'endAt'
])]
class Auction implements MessageableInterface
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FILLED = 'filled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS = [
        self::STATUS_ACTIVE,
        self::STATUS_FILLED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
        self::STATUS_SCHEDULED,
    ];

    public const TYPE_ENGLISH = 'english';
    public const TYPE_DUTCH = 'dutch';

    public const TYPES = [
        self::TYPE_ENGLISH,
        self::TYPE_DUTCH,
    ];

    public const GROUP_GET_AUCTIONS = 'get-auctions';
    public const GROUP_GET_AUCTION = 'get-auction';
    public const GROUP_GET_AUCTION_WITH_ASSET = 'get-auction-with-asset';
    public const GROUP_GET_AUCTION_WITH_BIDS = 'get-auction-with-bids';
    public const GROUP_POST_AUCTION = 'post-auction';
    public const GROUP_DELETE_AUCTION = 'delete-auction';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_GET_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(description: 'AuctionX internal ID of the auction', format: 'int')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(description: 'Type of the auction', enum: self::TYPES, example: self::TYPE_ENGLISH)]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_GET_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(description: 'Status of the auction', enum: self::STATUS)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'bigint', unique: true)]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION])]
    #[OA\Property(description: 'IMX transfer ID (asset deposit)', example: 4452442)]
    private string $transferId;

    #[ORM\Column(type: 'string')]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(description: 'Quantity of this asset (price)', example: "1000000000000000000")]
    private string $quantity;

    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(
        description: 'Number of decimals supported by this asset',
        format: 'int',
        example: 18,
    )]
    private int $decimals;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(description: 'Reserve quantity of this asset (reserve price)', example: "2000000000000000000")]
    private ?string $reserveQuantity;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(
        description: 'Currency of the auction',
        enum: TokenHelper::TOKENS,
        example: TokenHelper::TOKENS[0],
    )]
    private string $tokenType = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_GET_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(description: 'Address of the seller', example: '0xfd3268ce649945293a278c2f0dbd0849faa2d497')]
    private string $owner;

    #[ORM\ManyToOne(targetEntity: Asset::class, inversedBy: 'auctions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_GET_AUCTION_WITH_ASSET])]
    #[OA\Property(
        ref: '#/components/schemas/Asset.list',
        description: 'Asset related to the auction',
        type: 'object',
    )]
    private ?Asset $asset;

    #[ORM\OneToMany(mappedBy: 'auction', targetEntity: Bid::class, orphanRemoval: true)]
    #[Groups([self::GROUP_GET_AUCTION_WITH_BIDS])]
    #[OA\Property(
        description: 'Bids related to the auction',
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/Bid.list')
    )]
    private Collection $bids;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups([self::GROUP_GET_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(
        description: 'Created timestamp of this auction',
        type: 'string',
        format: 'datetime',
    )]
    protected \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(
        description: 'Start timestamp of this auction',
        type: 'string',
        format: 'datetime',
        example: '2030-12-01T00:00:00.000Z',
        nullable: true,
    )]
    private \DateTimeImmutable $startAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups([self::GROUP_GET_AUCTION, self::GROUP_POST_AUCTION, Asset::GROUP_GET_ASSET, Bid::GROUP_GET_BID])]
    #[OA\Property(
        description: 'End timestamp of this auction',
        type: 'string',
        format: 'datetime',
        example: '2030-12-31T23:59:59.999Z',
    )]
    private \DateTimeImmutable $endAt;

    public function __construct()
    {
        $this->bids = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTransferId(): ?string
    {
        return $this->transferId;
    }

    public function setTransferId(string $transferId): self
    {
        $this->transferId = $transferId;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDecimals(): ?int
    {
        return $this->decimals;
    }

    public function setDecimals(int $decimals): self
    {
        $this->decimals = $decimals;

        return $this;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function setTokenType(string $tokenType): self
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): self
    {
        $this->asset = $asset;

        return $this;
    }

    public function getEndAt(): \DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $dateFormat = $endAt->format('Y-m-d H:i:00');
        $dateImmutable = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateFormat);

        if ($dateImmutable) {
            $this->endAt = $dateImmutable;
        }

        return $this;
    }

    /**
     * @return Collection<int, Bid>
     */
    public function getBids(): Collection
    {
        return $this->bids;
    }

    public function addBid(Bid $bid): self
    {
        if (!$this->bids->contains($bid)) {
            $this->bids[] = $bid;
            $bid->setAuction($this);
        }

        return $this;
    }

    public function removeBid(Bid $bid): self
    {
        if ($this->bids->removeElement($bid)) {
            // set the owning side to null (unless already changed)
            if ($bid->getAuction() === $this) {
                $bid->setAuction(null);
            }
        }

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

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): self
    {
        $this->owner = strtolower($owner);

        return $this;
    }

    public function hasActiveBids(): bool
    {
        foreach ($this->getBids() as $bid) {
            if ($bid->getStatus() === Bid::STATUS_ACTIVE) {
                return true;
            }
        }

        return false;
    }

    public function getLastActiveBid(): ?Bid
    {
        foreach ($this->getBids() as $bid) {
            if ($bid->getStatus() === Bid::STATUS_ACTIVE) {
                return $bid;
            }
        }

        return null;
    }

    public function getLastBid(): Bid|false
    {
        return $this->getBids()->last();
    }

    public function getReserveQuantity(): ?string
    {
        return $this->reserveQuantity;
    }

    public function setReserveQuantity(?string $reserveQuantity): self
    {
        $this->reserveQuantity = $reserveQuantity;

        return $this;
    }

    public function getStartAt(): \DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $dateFormat = $startAt->format('Y-m-d H:i:00');
        $dateImmutable = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateFormat);

        if ($dateImmutable) {
            $this->startAt = $dateImmutable;
        }

        return $this;
    }
}
