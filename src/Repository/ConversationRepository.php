<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
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
     * @param int $userId
     * @param int $receiveId
     * @return array|null
     */
    public function getCountByUsersId(int $userId, int $receiveId): array
    {
        return $this->getEntityManager()->createQuery(
            'SELECT COUNT(u) FROM App:Conversation p JOIN p.userReferences u WHERE p.id = :userId AND u.id = :receiveId'
        )
            ->setParameter(':userId', $userId)
            ->setParameter(':receiveId', $receiveId)
            ->execute();
    }
}
