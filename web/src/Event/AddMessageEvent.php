<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AddMessageEvent
 * @package App\Event
 */
class AddMessageEvent extends Event
{
    public const NAME = 'added.message';

    /**
     * @var array
     */
    private $sendUsers;

    /**
     * AddMessageEvent constructor.
     * @param array $sendUsers
     */
    public function __construct(array $sendUsers)
    {
        $this->sendUsers = $sendUsers;
    }

    /**
     * @return array
     */
    public function getSendUsers(): array
    {
        return $this->sendUsers;
    }
}
