<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\Message;
use App\Enum\MessageStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
/**
 * TODO: Cover with a test.
 */
readonly class SendMessageHandler
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    public function __invoke(SendMessage $sendMessage): void
    {
        $message = new Message();
        $message->setUuid(Uuid::v6());
        $message->setText($sendMessage->text);
        $message->setStatus(MessageStatus::Sent);
        $message->setCreatedAt(new \DateTime());

        $this->manager->persist($message);
        $this->manager->flush();
    }
}
