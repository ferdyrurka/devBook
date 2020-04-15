<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\GetConversationListEvent;

/**
 * Class GetConversationListEventListener
 * @package App\EventListener
 */
class GetConversationListEventListener
{
    /**
     * @var array
     */
    private $conversations;

    /**
     * @param GetConversationListEvent $event
     */
    public function setConversations(GetConversationListEvent $event): void
    {
        $this->conversations = $event->getConversations();
    }

    /**
     * @return array
     */
    public function getConversations(): array
    {
        return $this->conversations;
    }
}
