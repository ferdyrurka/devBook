<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\GetMessageEvent;

/**
 * Class GetMessageEventListener
 * @package App\EventListener
 */
class GetMessageEventListener
{
    /**
     * @var array
     */
    private $messages;

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param GetMessageEvent $event
     */
    public function setMessages(GetMessageEvent $event): void
    {
        $this->messages = $event->getMessages();
    }
}
