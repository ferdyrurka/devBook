<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;
/**
 * Class RegistryOnlineUserCommand
 * @package App\Command\Console\DevMessenger
 */

class RegistryOnlineUserCommand implements CommandInterface
{
    /**
     * @var array
     */
    private $message;

    /**
     * @var int
     */
    private $connId;

    public function __construct(array $message, int $connId)
    {
        $this->message = $message;
        $this->connId = $connId;
    }

    /**
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getConnId(): int
    {
        return $this->connId;
    }
}
