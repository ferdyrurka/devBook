<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;

/**
 * Class GetMessageCommand
 * @package App\Command\API
 */
class GetMessageCommand implements CommandInterface
{
    /**
     * Max messages in one request.
     */
    public const LIMIT_MESSAGES = 30;

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
     * GetMessageCommand constructor.
     * @param int $userId
     * @param string $conversationId
     * @param int $offset
     */
    public function __construct(int $userId, string $conversationId, int $offset)
    {
        $this->userId = $userId;
        $this->conversationId = $conversationId;
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimitMessages(): int
    {
        return self::LIMIT_MESSAGES;
    }
}
