<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Auction;
use App\Entity\Bid;
use App\Entity\Message;
use App\Entity\MessageableInterface;
use App\Repository\MessageRepository;

class MessageService
{
    public function __construct(private readonly MessageRepository $messageRepository)
    {
    }

    public function transferNFT(
        string  $message,
        string  $internalId,
        string  $tokenId,
        string  $tokenAddress,
        string  $receiverAddress,
        Auction $auction
    ): void {
        $body = [
            'asset' => [
                'token_address' => $tokenAddress,
                'token_id' => $tokenId,
                'internal_id' => $internalId,
            ],
            'recipient' => $receiverAddress
        ];

        $this->createMessage($message, $body, $auction);
    }

    public function transferToken(
        string $message,
        string $tokenType,
        string $quantity,
        int    $decimals,
        string $receiverAddress,
        Bid    $bid
    ): void {
        $body = [
            'token' => [
                'token_type' => $tokenType,
                'quantity' => $quantity,
                'decimals' => $decimals,
            ],
            'recipient' => $receiverAddress
        ];

        $this->createMessage($message, $body, $bid);
    }

    private function createMessage(string $task, array $body, MessageableInterface $entity): void
    {
        $message = new Message();
        $message->setStatus(Message::STATUS_TODO);
        $message->setBody($body);
        $message->setTask($task);
        $message->setCreatedAt(new \DateTimeImmutable());

        $class = (new \ReflectionClass($entity))->getShortName();
        $message->{'set' . $class}($entity);

        $this->messageRepository->add($message);
    }
}
