<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Handler\HandlerInterface;
use App\Service\RedisService;

/**
 * Class DeleteOnlineUserCommand
 * @package App\Command\Console\DevMessenger
 */
class DeleteOnlineUserHandler implements HandlerInterface
{
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

    public function handle(CommandInterface $deleteOnlineUserCommand): void
    {
        $connId = $deleteOnlineUserCommand->getConnId();

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
