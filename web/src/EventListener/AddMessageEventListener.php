<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\AddMessageEvent;

/**
 * Class AddMessageEventListener
 * @package App\EventListener
 */
class AddMessageEventListener
{
    /**
     * @var array
     */
    private $sendUsers;

    /**
     * @return array
     */
    public function getSendUsers(): array
    {
        return $this->sendUsers;
    }

    /**
     * @param AddMessageEvent $event
     */
    public function setSendUsers(AddMessageEvent $event): void
    {
        $this->sendUsers = $event->getSendUsers();
    }
}
