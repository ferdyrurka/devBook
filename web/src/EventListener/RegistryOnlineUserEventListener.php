<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\RegistryOnlineUserEvent;

/**
 * Class RegistryOnlineUserEventListener
 * @package App\EventListener
 */
class RegistryOnlineUserEventListener
{
    /**
     * @var bool
     */
    private $result;

    /**
     * @param RegistryOnlineUserEvent $event
     */
    public function setResult(RegistryOnlineUserEvent $event): void
    {
        $this->result = $event->isResult();
    }

    /**
     * @return bool
     */
    public function isResult(): bool
    {
        return $this->result;
    }
}
