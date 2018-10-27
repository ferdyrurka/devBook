<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Exception\InvalidException;
use App\Handler\HandlerInterface;
use App\Repository\MessageRepository;
use App\Util\ConversationIdValidator;

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
     * @var array
     * In variables held result are send data in messageController API.
     * Keys in table is Template (From|Receive), messages and date time in format Y-m-d H:i:s.
     */
    private $result = [];

    /**
     * GetMessageCommand constructor.
     * @param MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
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

        foreach ($messages as $message) {
            $this->result[$i]['message'] = (string) $message->getMessage();
            $this->result[$i]['date'] = (string) $message->getSendTime()->format('Y-m-d H:i:s');

            if ($userId !== $message->getSendUserId()) {
                $this->result[$i]['template'] = 'Receive';
                $i++;
                continue;
            }

            $this->result[$i]['template'] = 'From';
            $i++;
            continue;
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
