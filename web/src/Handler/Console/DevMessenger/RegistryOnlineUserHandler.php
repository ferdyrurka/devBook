<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Event\RegistryOnlineUserEvent;
use App\Exception\UserNotFoundException;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;
use App\Service\RedisService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var
     */
    private $eventDispatcher;

    /**
     * RegistryOnlineUserHandler constructor.
     * @param UserRepository $userRepository
     * @param RedisService $redisService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        UserRepository $userRepository,
        RedisService $redisService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
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
            $user = $this->userRepository->getOneByPrivateTokens($userToken);
        } catch (UserNotFoundException $exception) {
            $this->sendNotification(false);
            return;
        }

        /**
         * Table users by conn id
         */

        $connId = $registryUserCommand->getConnId();

        if ($userByConn->exists($connId) > 0) {
            $this->sendNotification(false);
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

        $this->sendNotification(true);
    }

    /**
     * @param bool $result
     */
    private function sendNotification(bool $result): void
    {
        $event = new RegistryOnlineUserEvent($result);
        $this->eventDispatcher->dispatch(RegistryOnlineUserEvent::NAME, $event);
    }
}
