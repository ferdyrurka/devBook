<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class RegistryOnlineUserEvent
 * @package App\Event
 */
class RegistryOnlineUserEvent extends Event
{
    public const NAME = 'registry.online.user';

    /**
     * @var bool
     */
    private $result;

    /**
     * RegistryOnlineUserEvent constructor.
     * @param bool $result
     */
    public function __construct(bool $result)
    {
        $this->result = $result;
    }

    /**
     * @return bool
     */
    public function isResult(): bool
    {
        return $this->result;
    }
}
