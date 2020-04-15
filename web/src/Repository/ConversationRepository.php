<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use App\Exception\ConversationNotExistException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ConversationRepository
 * @package App\Repository
 */
class ConversationRepository extends ServiceEntityRepository
{
    /**
     * ConversationRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * @param string $conversationId
     * @return array
     * @throws ConversationNotExistException
     */
    public function getByConversationId(string $conversationId): array
    {
        $conversation = $this->findBy(['conversationId' => $conversationId]);

        if (empty($conversation)) {
            throw new ConversationNotExistException(
                'Conversation by conversationId: ' . $conversationId . 'not found!'
            );
        }

        return $conversation;
    }

    /**
     * @param string $conversationId
     * @param int $userId
     * @return array|null
     */
    public function findConversationByConversationIdAndUserId(string $conversationId, int $userId): ?array
    {
        return $this->getEntityManager()->createQuery('
            SELECT p FROM App:Conversation p INNER JOIN p.userReferences u
            WHERE u.id = :userId AND p.conversationId = :conversationId
        ')
            ->setParameter(':userId', $userId)
            ->setParameter(':conversationId', $conversationId)
            ->execute();
    }

    /**
     * @param Conversation $conversation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Conversation $conversation): void
    {
        $this->getEntityManager()->persist($conversation);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Conversation $conversation
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Conversation $conversation): void
    {
        $this->getEntityManager()->remove($conversation);
        $this->getEntityManager()->flush();
    }
}
