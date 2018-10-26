<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Entity\User;
use App\Handler\HandlerInterface;
use App\Repository\MessageRepository;

/**
 * Class GetConversationListCommand
 * @package App\Command\API
 */
class GetConversationListHandler implements HandlerInterface
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
     * @var array
     */
    private $result = [];

    /**
     * @param CommandInterface $getConversationListCommand
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function handle(CommandInterface $getConversationListCommand): void
    {
        $user = $getConversationListCommand->getUser();
        $conversations = $user->getConversationReferences()->getValues();

        $i = 0;

        foreach ($conversations as $conversation) {
            $conversationId = $conversation->getConversationId();
            $message = $this->messageRepository->getLastMessageByConversationId($conversationId);
            $usersConversation = $conversation->getUserReferences()->getValues();

            foreach ($usersConversation as $userConversation) {
                if ($user->getId() !== $userConversation->getId()) {
                    $this->result[$i]['fullName'] = $userConversation->getFirstName() . ' ' .
                        $userConversation->getSurname()
                    ;

                    /**
                     * Break because in conversation two users, one logged and second
                     * which other id than what is logged.
                     */
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
