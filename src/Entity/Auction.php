<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Utils\TimestampTrait;
use App\Helper\TokenHelper;
use App\Repository\AuctionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: AuctionRepository::class)]
#[OA\Schema(description: 'Auction linked to an asset')]
class Auction
{
    use TimestampTrait;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_FILLED = 'filled';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const STATUS = [
        self::STATUS_ACTIVE,
        self::STATUS_FILLED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
    ];

    public const TYPE_ENGLISH = 'english';
    public const TYPE_DUTCH = 'dutch';

    public const TYPES = [
        self::TYPE_ENGLISH,
        self::TYPE_DUTCH,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('auction')]
    #[OA\Property(description: 'Auction X internal ID of the auction', format: 'int')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('auction')]
    #[OA\Property(description: 'Type of the auction', format: 'string', enum: self::TYPES)]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('auction')]
    #[OA\Property(description: 'Status of the auction', format: 'string', enum: self::STATUS)]
    private string $status;

    #[ORM\Column(type: 'bigint')]
    #[Groups('auction')]
    #[OA\Property(description: 'IMX transfer ID (asset deposit)', format: 'string')]
    private string $transferId;

    #[ORM\Column(type: 'bigint')]
    #[Groups('auction')]
    #[OA\Property(description: 'Quantity of this asset (price)', format: 'string')]
    private string $quantity;

    #[ORM\Column(type: 'integer')]
    #[Groups('auction')]
    #[OA\Property(description: 'Number of decimals supported by this asset', format: 'int')]
    private int $decimals;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups('auction')]
    #[OA\Property(description: 'Currency of the auction', format: 'string', enum: TokenHelper::TOKENS)]
    private string $tokenType;

    #[ORM\ManyToOne(targetEntity: Asset::class, inversedBy: 'auctions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('auction')]
    #[OA\Property(description: 'Asset related to the auction')]
    private ?Asset $asset;

    #[ORM\Column(type: 'datetime')]
    #[Groups('auction')]
    #[OA\Property(description: 'End timestamp of this auction', type: 'string', format: 'date-time')]
    private ?\DateTimeInterface $endAt;

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

    public function getTokenType(): ?string
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

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt = null): self
    {
        $this->endAt = $endAt;

        return $this;
    }
}
