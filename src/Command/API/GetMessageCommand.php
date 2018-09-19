<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;
use App\Exception\InvalidException;
use App\Repository\MessageRepository;
use App\Util\MessageIdValidator;

/**
 * Class GetMessageCommand
 * @package App\Command\API
 */
class GetMessageCommand implements CommandInterface
{

    /**
     * Max messages in one request.
     */
    const LIMIT_MESSAGES = 30;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $conversationId;

    /**
     * @var int
     */
    private $offset;

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
     * @return int
     */
    private function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    /**
     * @param string $conversationId
     */
    public function setConversationId(string $conversationId): void
    {
        $this->conversationId = $conversationId;
    }

    /**
     * @return int
     */
    private function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset * self::LIMIT_MESSAGES;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @throws InvalidException
     */
    public function execute(): void
    {
        $validator = new MessageIdValidator();
        if ($validator->validate($conversationId = $this->getConversationId()) == false) {
            throw new InvalidException('This Conversation id is invalid. Conversation id is ' . $conversationId);
        }

        $messages = $this->messageRepository->findByConversationId(
            $this->getConversationId(),
            $this->getOffset(),
            self::LIMIT_MESSAGES
        );

        if (is_null($messages)) {
            return;
        }

        $userId = $this->getUserId();
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

        return;
    }
}
