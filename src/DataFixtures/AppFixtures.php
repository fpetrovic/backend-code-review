<?php

namespace App\DataFixtures;

use App\Entity\Message;
use App\Enum\MessageStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Uid\Uuid;

use function Psl\Iter\random;

class AppFixtures extends Fixture
{
    public const MESSAGES_COUNT = 10;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        foreach (range(1, self::MESSAGES_COUNT) as $i) {
            $message = new Message();
            $message->setUuid(Uuid::v6());
            $message->setText($faker->sentence);
            $message->setStatus(random([MessageStatus::Sent, MessageStatus::Read]));
            $message->setCreatedAt(new \DateTime());

            $manager->persist($message);
        }

        $manager->flush();
    }
}
