<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Message;
use App\Helper\TokenHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MessageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {

            $task = Message::TASKS[rand(0, count(Message::TASKS) - 1)];

            if ($task === Message::TASK_TRANSFER_NFT) {
                $body = [
                    'asset' => [
                        'token_address' => '0xb0e827c9ab5e68d243f707f832b756981987f704',
                        'token_id' => '1234',
                    ],
                    'recipient' => '0xb8f6577961ff927c70d26ac7b691474e5a8e2927'
                ];
            } else {
                $body = [
                    'asset' => [
                        'token_type' => TokenHelper::TOKENS[0],
                        'quantity' => '1000000000000',
                        'decimals' => 18,
                    ],
                    'recipient' => '0xb8f6577961ff927c70d26ac7b691474e5a8e2927'
                ];
            }

            $message = new Message();
            $message->setStatus(Message::STATUS_TODO);
            $message->setTask($task);
            $message->setBody($body);
            $message->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($message);
        }

        $manager->flush();
    }
}
