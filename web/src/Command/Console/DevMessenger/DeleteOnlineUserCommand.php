<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Service\RedisService;

/**
 * Class DeleteOnlineUserCommand
 * @package App\Command\Console\DevMessenger
 */
class DeleteOnlineUserCommand implements CommandInterface
{
    /**
     * @var integer
     */
    private $connId;

    public function __construct(int $connId)
    {
        $this->connId = $connId;
    }

    /**
     * @return int
     */
    public function getConnId(): int
    {
        return $this->connId;
    }
}
