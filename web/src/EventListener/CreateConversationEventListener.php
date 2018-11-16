<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\CreateConversationEvent;

/**
 * Class CreateConversationEventListener
 * @package App\EventListener
 */
class CreateConversationEventListener
{
    /**
     * @var array
     */
    private $conversation;

    /**
     * @param CreateConversationEvent $event
     */
    public function setConversation(CreateConversationEvent $event): void
    {
        $this->conversation = $event->getConversation();
    }

    /**
     * @return array
     */
    public function getConversation(): array
    {
        return $this->conversation;
    }
}
