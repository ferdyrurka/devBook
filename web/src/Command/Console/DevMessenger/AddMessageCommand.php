<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Exception\ConversationNotExistException;
use App\Exception\NotAuthorizationUUIDException;
use App\Exception\UserNotFoundException;
use App\Exception\UserNotFoundInConversationException;
use App\Repository\ConversationRepository;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AddMessageCommand
 * @package App\Command\Console\DevMessenger
 * Not set database in __constructor because WebSocket using pattern Singleton and not working variables.
 */
class AddMessageCommand implements CommandInterface
{
    /**
     * @var array
     */
    private $message;

    /**
     * @var int
     */
    private $fromId;

    /**
     * AddMessageCommand constructor.
     * @param array $message
     * @param int $fromId
     */
    public function __construct(array $message, int $fromId)
    {
        $this->message = $message;
        $this->fromId = $fromId;
    }

    /**
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getFromId(): int
    {
        return $this->fromId;
    }
}
