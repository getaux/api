<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BidRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: BidRepository::class)]
#[OA\Schema(description: 'Bids linked to an asset')]
class Bid
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

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Auction::class, inversedBy: 'bids')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Auction $auction;

    #[ORM\Column(type: 'string', length: 255)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'bigint')]
    private string $transferId;

    #[ORM\Column(type: 'bigint')]
    private string $quantity;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $updatedAt;

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

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
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
}
