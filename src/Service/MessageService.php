<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Message;
use App\Repository\MessageRepository;

class MessageService
{
    public function __construct(private readonly MessageRepository $messageRepository)
    {
    }

    public function transferNFT(
        string $tokenId,
        string $tokenAddress,
        string $receiverAddress
    ): void
    {
        $body = [
            'asset' => [
                'token_address' => $tokenAddress,
                'token_id' => $tokenId,
            ],
            'recipient' => $receiverAddress
        ];

        $this->createMessage(Message::TASK_TRANSFER_NFT, $body);
    }

    public function transferCrypto(
        string $tokenType,
        string $quantity,
        int    $decimals,
        string $receiverAddress
    ): void
    {
        $body = [
            'asset' => [
                'token_type' => $tokenType,
                'quantity' => $quantity,
                'decimals' => $decimals,
            ],
            'recipient' => $receiverAddress
        ];

        $this->createMessage(Message::TASK_TRANSFER_CRYPTO, $body);
    }

    private function createMessage(string $task, array $body): void
    {
        $message = new Message();
        $message->setStatus(Message::STATUS_TODO);
        $message->setBody($body);
        $message->setTask($task);
        $message->setCreatedAt(new \DateTimeImmutable());

        $this->messageRepository->add($message);
    }
}