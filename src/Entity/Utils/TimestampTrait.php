<?php

declare(strict_types=1);

namespace App\Entity\Utils;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait TimestampTrait
{
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('auction')]
    protected \DateTimeImmutable $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    protected \DateTime $updatedAt;

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}