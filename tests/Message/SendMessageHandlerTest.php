<?php

declare(strict_types=1);

namespace App\Tests\Message;

use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SendMessageHandlerTest extends KernelTestCase
{
    private SendMessageHandler $sendMessageHandler;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sendMessageHandler = new SendMessageHandler($this->entityManager);
    }

    public function testItHandlesTheMessage(): void
    {
        $text = 'Test message';
        $sendMessage = new SendMessage($text);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) use ($text) {
                return $entity instanceof Message && $entity->getText() === $text;
            }));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->sendMessageHandler->__invoke($sendMessage);
    }
}
