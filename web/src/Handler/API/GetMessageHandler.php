<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Event\GetMessageEvent;
use App\Exception\InvalidException;
use App\Handler\HandlerInterface;
use App\Repository\MessageRepository;
use App\Util\ConversationIdValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class GetMessageCommand
 * @package App\Command\API
 */
class GetMessageHandler implements HandlerInterface
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
     * GetMessageHandler constructor.
     * @param MessageRepository $messageRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(MessageRepository $messageRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CommandInterface $getMessageCommand
     * @throws InvalidException
     */
    public function handle(CommandInterface $getMessageCommand): void
    {
        $validator = new ConversationIdValidator();
        if ($validator->validate($conversationId = $getMessageCommand->getConversationId()) === false) {
            throw new InvalidException('This Conversation id is invalid. Conversation id is ' . $conversationId);
        }

        $limitMessages = $getMessageCommand->getLimitMessages();

        $messages = $this->messageRepository->findByConversationId(
            $conversationId,
            $getMessageCommand->getOffset() * $limitMessages,
            $limitMessages
        );

        if ($messages === null) {
            return;
        }

        $userId = $getMessageCommand->getUserId();
        $i = 0;
        /**
         * @var array
         * In variables held result are send data in messageController API.
         * Keys in table is Template (From|Receive), messages and date time in format Y-m-d H:i:s.
         */
        $result = [];

        foreach ($messages as $message) {
            $result[$i]['message'] = (string) $message->getMessage();
            $result[$i]['date'] = (string) $message->getSendTime()->format('Y-m-d H:i:s');

            if ($userId !== $message->getSendUserId()) {
                $result[$i]['template'] = 'Receive';
                $i++;
                continue;
            }

            $result[$i]['template'] = 'From';
            $i++;
            continue;
        }

        $event = new GetMessageEvent($result);
        $this->eventDispatcher->dispatch(GetMessageEvent::NAME, $event);
    }
}
