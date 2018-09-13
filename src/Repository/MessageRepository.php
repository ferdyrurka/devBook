<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class MessageRepository
 * @package App\Repository
 */
class MessageRepository extends ServiceEntityRepository
{
    /**
     * MessageRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @param string $conversationId
     * @param int $offset
     * @param int $limit
     * @return array|null
     */
    public function findByMessageId(string $conversationId, int $offset, int $limit): ?array
    {
        return $this->getEntityManager()->createQuery('
            SELECT p FROM App:Message p WHERE 
            p.conversationId = :conversationId ORDER BY p.sendTime ASC')
            ->setParameter(':conversationId', $conversationId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->execute()
        ;
    }
}
