<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetConversationListEvent
 * @package App\Event
 */
class GetConversationListEvent extends Event
{
    public const NAME = 'get.conversation.list';

    /**
     * @var array
     */
    private $conversations;

    /**
     * GetConversationListEvent constructor.
     * @param array $conversations
     */
    public function __construct(array $conversations)
    {
        $this->conversations = $conversations;
    }

    /**
     * @return array
     */
    public function getConversations(): array
    {
        return $this->conversations;
    }
}
