<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Event\GetConversationListEvent;
use App\Handler\HandlerInterface;
use App\Repository\MessageRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * GetConversationListHandler constructor.
     * @param MessageRepository $messageRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(MessageRepository $messageRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param CommandInterface $getConversationListCommand
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function handle(CommandInterface $getConversationListCommand): void
    {
        $user = $getConversationListCommand->getUser();
        $conversations = $user->getConversationReferences()->getValues();

        $result = [];
        $i = 0;

        foreach ($conversations as $conversation) {
            $conversationId = $conversation->getConversationId();
            $message = $this->messageRepository->getLastMessageByConversationId($conversationId);
            $usersConversation = $conversation->getUserReferences()->getValues();

            foreach ($usersConversation as $userConversation) {
                if ($user->getId() !== $userConversation->getId()) {
                    $result[$i]['fullName'] = $userConversation->getFirstName() . ' ' .
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

            $result[$i]['lastMessage'] = $message->getMessage();
            $result[$i]['conversationId'] = $conversationId;

            $i++;
        }

        $event = new GetConversationListEvent($result);
        $this->eventDispatcher->dispatch(GetConversationListEvent::NAME, $event);
    }
}
