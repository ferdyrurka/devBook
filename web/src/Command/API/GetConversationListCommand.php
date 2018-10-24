<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;
use App\Entity\User;
use App\Repository\MessageRepository;

/**
 * Class GetConversationListCommand
 * @package App\Command\API
 */
class GetConversationListCommand implements CommandInterface
{

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * GetConversationListCommand constructor.
     * @param MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $result = [];

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    private function getUser(): User
    {
        return $this->user;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function execute(): void
    {
        $user = $this->getUser();
        $conversations = $user->getConversationReferences()->getValues();

        $i = 0;

        foreach ($conversations as $conversation) {
            $conversationId = $conversation->getConversationId();
            $message = $this->messageRepository->getLastMessageByConversationId($conversationId);
            $usersConversation = $conversation->getUserReferences()->getValues();

            foreach ($usersConversation as $userConversation) {
                if ($user->getId() != $userConversation->getId()) {
                    $this->result[$i]['fullName'] = $userConversation->getFirstName() . ' ' .
                        $userConversation->getSurname()
                    ;

                    break;
                }

                continue;
            }

            $this->result[$i]['lastMessage'] = $message->getMessage();
            $this->result[$i]['conversationId'] = $conversationId;

            $i++;
        }
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
