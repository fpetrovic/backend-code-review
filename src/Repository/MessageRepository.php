<?php

namespace App\Repository;

use App\Entity\Message;
use App\Enum\MessageStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return array<int, array{uuid: string, text: string, status: string}>
     **/
    public function by(?MessageStatus $status = null): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('m.uuid', 'm.text', 'm.status');

        if ($status) {
            $queryBuilder->where('m.status = :status')
                ->setParameter('status', $status);
        }

        /** @var array<int, array{uuid: string, text: string, status: string}> $results */
        $results = $queryBuilder->getQuery()->getResult();

        return $results;
    }
}
