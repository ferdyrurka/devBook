<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CreateConversationEvent
 * @package App\Event
 */
class CreateConversationEvent extends Event
{
    public const NAME = 'create.conversation';

    /**
     * @var array
     */
    private $conversation;

    /**
     * CreateConversationEvent constructor.
     * @param array $conversation
     */
    public function __construct(array $conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * @return array
     */
    public function getConversation(): array
    {
        return $this->conversation;
    }
}
