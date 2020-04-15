<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class GetMessageEvent
 * @package App\Event
 */
class GetMessageEvent extends Event
{
    public const NAME = 'get.message';

    /**
     * @var array
     */
    private $messages;

    /**
     * GetMessageEvent constructor.
     * @param array $messages
     */
    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
