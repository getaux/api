<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in-progress';
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';

    public const STATUS = [
        self::STATUS_TODO,
        self::STATUS_IN_PROGRESS,
        self::STATUS_DONE,
        self::STATUS_ERROR,
    ];

    public const TASK_TRANSFER_TOKEN = 'task-transfer-token';
    public const TASK_TRANSFER_NFT = 'task-transfer-nft';

    public const TASKS = [
        self::TASK_TRANSFER_TOKEN,
        self::TASK_TRANSFER_NFT,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $task;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(type: 'text')]
    private string $status = self::STATUS_TODO;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $response;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deliveredAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $processedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): mixed
    {
        return json_decode($this->body, true);
    }

    public function setBody(array $body): self
    {
        $this->body = (string)json_encode($body);

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

    public function getResponse(): mixed
    {
        if ($this->response) {
            return json_decode($this->response, true);
        } else {
            return null;
        }
    }

    public function setResponse(array $response): self
    {
        $this->response = (string)json_encode($response);

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

    public function getDeliveredAt(): ?\DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(\DateTimeImmutable $deliveredAt): self
    {
        $this->deliveredAt = $deliveredAt;

        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(\DateTimeImmutable $processedAt): self
    {
        $this->processedAt = $processedAt;

        return $this;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(string $task): self
    {
        $this->task = $task;

        return $this;
    }
}
