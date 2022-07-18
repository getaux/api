<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BidRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BidRepository::class)]
#[OA\Schema(description: 'Bids linked to an asset', required: [
    'transferId', 'quantity', 'decimals', 'tokenType'
])]
class Bid implements MessageableInterface
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_OVERPAID = 'overpaid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_INVALID = 'invalid';

    public const STATUS = [
        self::STATUS_ACTIVE,
        self::STATUS_OVERPAID,
        self::STATUS_CANCELLED,
        self::STATUS_INVALID,
    ];

    public const GROUP_GET_BIDS = 'get-bids';
    public const GROUP_GET_BID = 'get-bid';
    public const GROUP_POST_BID = 'post-bid';
    public const GROUP_GET_BID_WITH_AUCTION = 'get-bid-with-auction';
    public const GROUP_DELETE_BID = 'delete-bid';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_GET_BID, Auction::GROUP_GET_AUCTION])]
    #[OA\Property(description: 'AuctionX internal ID of the bid', format: 'int')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Auction::class, inversedBy: 'bids')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_GET_BID_WITH_AUCTION])]
    #[OA\Property(
        ref: '#/components/schemas/Auction.list',
        description: 'Auction related to the bid',
        type: 'object',
    )]
    private ?Auction $auction;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_GET_BID, Auction::GROUP_GET_AUCTION])]
    #[OA\Property(description: 'Status of the bid', enum: self::STATUS)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'bigint')]
    #[Groups([self::GROUP_GET_BID])]
    #[OA\Property(description: 'IMX transfer ID (bid deposit)', example: 4452442)]
    private string $transferId;

    #[ORM\Column(type: 'bigint')]
    #[Groups([self::GROUP_GET_BID, Auction::GROUP_GET_AUCTION])]
    #[OA\Property(description: 'Quantity of this bid (price)', example: "1000000000000000000")]
    private string $quantity = '0';

    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_GET_BID, Asset::GROUP_GET_ASSET])]
    #[OA\Property(
        description: 'Number of decimals supported by this bid',
        format: 'int',
        example: 18,
    )]
    private int $decimals = 0;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_GET_BID, Auction::GROUP_GET_AUCTION])]
    #[OA\Property(description: 'Address of the bidder', example: '0xfd3268ce649945293a278c2f0dbd0849faa2d497')]
    private string $owner;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups([self::GROUP_GET_BID, Auction::GROUP_GET_AUCTION])]
    #[OA\Property(
        description: 'Created timestamp of this id',
        type: 'string',
        format: 'datetime',
        example: '2030-12-31T23:59:59.999Z',
    )]
    protected \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups([self::GROUP_GET_BID, Auction::GROUP_GET_AUCTION])]
    #[OA\Property(
        description: 'End timestamp of this bid',
        type: 'string',
        format: 'datetime',
        example: '2030-12-31T23:59:59.999Z',
    )]
    private \DateTimeImmutable $endAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuction(): ?Auction
    {
        return $this->auction;
    }

    public function setAuction(?Auction $auction): self
    {
        $this->auction = $auction;

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

    public function getQuantity(): string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function setDecimals(int $decimals): self
    {
        $this->decimals = $decimals;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
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

    public function getEndAt(): \DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        if (!$endAt) {
            $endAt = new \DateTime('+7 days');
        }

        $dateFormat = $endAt->format('Y-m-d H:i:00');
        $dateImmutable = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateFormat);

        if ($dateImmutable) {
            $this->endAt = $dateImmutable;
        }

        return $this;
    }
}
