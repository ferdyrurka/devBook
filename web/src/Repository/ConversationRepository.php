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
     * @return Conversation
     * @throws ConversationNotExistException
     */
    public function getByConversationId(string $conversationId): Conversation
    {
        $conversation = $this->findBy(['conversationId' => $conversationId]);

        if (empty($conversation)) {
            throw new ConversationNotExistException(
                'Conversation by conversationId: ' . $conversationId . 'not found!'
            );
        }

        return $conversation;
    }
}
