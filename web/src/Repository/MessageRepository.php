<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message;
use App\Repository\NullObject\NullMessageRepository;
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
    public function findByConversationId(string $conversationId, int $offset, int $limit): ?array
    {
        return $this->getEntityManager()->createQuery('
            SELECT p FROM App:Message p WHERE 
            p.conversationId = :conversationId ORDER BY p.sendTime DESC')
            ->setParameter(':conversationId', $conversationId)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->execute()
        ;
    }

    /**
     * @param string $conversationId
     * @return Message
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastMessageByConversationId(string $conversationId): Message
    {
        $message = $this->getEntityManager()->createQuery('
            SELECT p FROM App:Message p WHERE
            p.conversationId = :conversationId ORDER BY p.sendTime DESC
        ')
            ->setParameter(':conversationId', $conversationId)
            ->setMaxResults(1)
            ->getOneOrNullResult()
            ;

        if (is_null($message)) {
            $nullMessage = new NullMessageRepository();
            return $nullMessage->getLastMessageByConversationId('');
        }

        return $message;
    }

    /**
     * @param Message $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Message $message): void
    {
        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Message $message
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Message $message): void
    {
        $this->getEntityManager()->remove($message);
        $this->getEntityManager()->flush();
    }
}
