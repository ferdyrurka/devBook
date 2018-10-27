<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Exception\UserNotFoundException;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;
use App\Service\RedisService;

/**
 * Class RegistryOnlineUserCommand
 * @package App\Command\Console\DevMessenger
 */

class RegistryOnlineUserHandler implements HandlerInterface
{
    /**
     * @var RedisService
     */
    private $redisService;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var bool
     */
    private $result = true;

    /**
     * RegistryOnlineUserCommand constructor.
     * @param UserRepository $userRepository
     * @param RedisService $redisService
     */
    public function __construct(UserRepository $userRepository, RedisService $redisService)
    {
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CommandInterface $registryUserCommand
     */
    public function handle(CommandInterface $registryUserCommand): void
    {
        $userByConn = $this->redisService->setDatabase(0);
        $message = $registryUserCommand->getMessage();

        $userToken = htmlspecialchars($message['userId']);

        try {
            $user = $this->userRepository->getOneByPrivateWebToken($userToken);
        } catch (UserNotFoundException $exception) {
            $this->result = false;
            return;
        }

        /**
         * Table users by conn id
         */

        $connId = $registryUserCommand->getConnId();

        if ($userByConn->exists($connId) > 0) {
            $this->result = false;

            return;
        }

        $userByConn->set($connId, $userToken);

        /**
         * Table users by Uuid
         */

        $userByUuidRedis = $this->redisService->setDatabase(1);

        if ($userByUuidRedis->exists($userToken) > 0) {
            return;
        }

        $userByUuidRedis->set($userToken, json_encode([
            'connId' => $connId,
            'id' => $user->getId()
        ]));
    }

    /**
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->result;
    }
}
