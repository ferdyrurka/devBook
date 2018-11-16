<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AddNotificationNewMessageEvent
 * @package App\Event
 */
class AddNotificationNewMessageEvent extends Event
{
    public const NAME = 'add.notification.new.message';

    /**
     * @var boolean
     */
    private $send;

    /**
     * AddNotificationNewMessageEvent constructor.
     * @param bool $send
     */
    public function __construct(bool $send)
    {
        $this->send = $send;
    }

    /**
     * @return bool
     */
    public function isSend(): bool
    {
        return $this->send;
    }
}
