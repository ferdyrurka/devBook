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

    /**
     * @var RedisService
     */
    private $redisService;

    /**
     * DeleteOnlineUserCommand constructor.
     * @param RedisService $redisService
     */
    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

    /**
     * @param int $connId
     */
    public function setConnId(int $connId): void
    {
        $this->connId = $connId;
    }

    /**
     * @return int
     */
    private function getConnId(): int
    {
        return $this->connId;
    }

    public function execute(): void
    {
        $connId = $this->getConnId();

        $userByConnRedis = $this->redisService->setDatabase(0);
        $userUuid = $userByConnRedis->get($connId);

        if (empty($userUuid)) {
            return;
        }

        $userByConnRedis->del([$connId]);
        $userByUuidRedis = $this->redisService->setDatabase(1);

        if ($userByUuidRedis->exists($userUuid) === 0) {
            return;
        }

        $userByUuidRedis->del([$userUuid]);
    }
}
