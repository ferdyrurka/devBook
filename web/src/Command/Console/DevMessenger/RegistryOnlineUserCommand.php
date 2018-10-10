<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use App\Service\RedisService;

/**
 * Class RegistryOnlineUserCommand
 * @package App\Command\Console\DevMessenger
 */

class RegistryOnlineUserCommand implements CommandInterface
{
    /**
     * @var RedisService
     */
    private $redisService;

    /**
     * @var array
     */
    private $message;

    /**
     * @var int
     */
    private $connId;

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
     * @return int
     */
    private function getConnId(): int
    {
        return $this->connId;
    }

    /**
     * @param int $connId
     */
    public function setConnId(int $connId): void
    {
        $this->connId = $connId;
    }

    /**
     * @param array $message
     * @return RegistryOnlineUserCommand
     */
    public function setMessage(array $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    private function getMessage(): array
    {
        return $this->message;
    }

    public function execute(): void
    {
        $userByConn = $this->redisService->setDatabase(0);
        $message = $this->getMessage();

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

        $connId = $this->getConnId();

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
