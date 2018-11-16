<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\AddNotificationNewMessageEvent;

/**
 * Class AddNotificationNewMessageEventListener
 * @package App\EventListener
 */
class AddNotificationNewMessageEventListener
{
    /**
     * @var bool
     */
    private $send;

    /**
     * @return bool
     */
    public function isSend(): bool
    {
        return $this->send;
    }

    /**
     * @param AddNotificationNewMessageEvent $event
     */
    public function setSend(AddNotificationNewMessageEvent $event): void
    {
        $this->send = $event->isSend();
    }
}
