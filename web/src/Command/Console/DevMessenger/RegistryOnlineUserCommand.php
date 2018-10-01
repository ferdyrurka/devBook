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
     * @var \Predis\Client
     */
    private $redis;

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
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        $this->redisService = new RedisService(0);
        $this->redis = $this->redisService->getClient();
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

        if ($this->redis->exists($userToken) > 0) {
            $this->result = false;
            return;
        }

        $connId = $this->getConnId();

        $this->redis->set($connId, $userToken);

        /**
         * Table users by Uuid
         */

        $this->redis = $this->redisService->setDatabase(1);

        if ($this->redis->exists($userToken) > 0) {
            return;
        }

        $this->redis->set($userToken, json_encode([
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
