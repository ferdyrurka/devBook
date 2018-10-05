<?php
declare(strict_types=1);

namespace App\Service;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
use App\Entity\Message;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Class DevMessengerService
 * @package App\Service
 */
class DevMessengerService implements MessageComponentInterface
{

    /**
     * @var \SplObjectStorage
     */
    private $clients;

    /**
     * @var array
     */
    private $users = [];

    /**
     * @var array
     */
    private $conversation = [];

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @var AddMessageCommand
     */
    private $addMessageCommand;

    /**
     * @var CreateConversationCommand
     */
    private $createConversationCommand;

    /**
     * @var RegistryOnlineUserCommand
     */
    private $registryOnlineUserCommand;

    /**
     * DevMessengerService constructor.
     * @param CommandService $commandService
     * @param AddMessageCommand $addMessageCommand
     * @param CreateConversationCommand $createConversationCommand
     * @param RegistryOnlineUserCommand $registryOnlineUserCommand
     */
    public function __construct(
        CommandService $commandService,
        AddMessageCommand $addMessageCommand,
        CreateConversationCommand $createConversationCommand,
        RegistryOnlineUserCommand $registryOnlineUserCommand
    ) {
        $this->clients = new \SplObjectStorage();

        $this->registryOnlineUserCommand = $registryOnlineUserCommand;
        $this->addMessageCommand = $addMessageCommand;
        $this->commandService = $commandService;
        $this->createConversationCommand = $createConversationCommand;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $msg = json_decode($msg, true);

        if (!array_key_exists('type', $msg) && !array_key_exists('userId', $msg)) {
            return;
        }

        switch (htmlspecialchars($msg['type'])) {

            /**
             * Users is online
             * Register in array Users
             */
            case 'registry':
                $this->registryOnlineUserCommand->setConnId($from->resourceId);
                $this->registryOnlineUserCommand->setMessage($msg);

                $this->commandService->setCommand($this->registryOnlineUserCommand);
                $this->commandService->execute();

                if ($this->commandService->getResult() === false) {
                    $this->onClose($from);
                }

                break;
            /**
             * Users send messages.
             * Send to users or alert (RabbitMQ).
             */

            case 'message':
                $this->addMessageCommand->setMessage($msg);
                $this->addMessageCommand->setFromId($from->resourceId);

                $this->commandService->setCommand($this->addMessageCommand);
                $this->commandService->execute();

                foreach ($this->commandService->getResult() as $userId) {
                    //User is online
                    if ($userId !== $msg['userId'] && array_key_exists($userId, $this->users)) {
                        $this->users[$userId]['conn']->send(json_encode([
                            'type' => 'message',
                            'message' => $msg['message']
                        ]));
                    }
                }

                return;
            case 'create':
                $this->createConversationCommand->setReceiveUserToken(htmlspecialchars($msg['receiveId']));
                $this->createConversationCommand->setSendUserToken(htmlspecialchars($msg['userId']));

                $this->commandService->setCommand($this->createConversationCommand);
                $this->commandService->execute();

                $result = $this->commandService->getResult();

                if (!empty($result)) {
                    $result['type'] = 'create';
                }

                $from->send(json_encode($result));
                return;
            default:
                return;
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
