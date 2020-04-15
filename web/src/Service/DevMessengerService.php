<?php
declare(strict_types=1);

namespace App\Service;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Command\Console\DevMessenger\AddNotificationNewMessageCommand;
use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Command\Console\DevMessenger\DeleteOnlineUserCommand;
use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
use App\Event\AddMessageEvent;
use App\Event\AddNotificationNewMessageEvent;
use App\Event\CreateConversationEvent;
use App\Event\RegistryOnlineUserEvent;
use App\EventListener\AddMessageEventListener;
use App\EventListener\AddNotificationNewMessageEventListener;
use App\EventListener\CreateConversationEventListener;
use App\EventListener\RegistryOnlineUserEventListener;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private $users;

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * DevMessengerService constructor.
     * @param CommandService $commandService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(CommandService $commandService, EventDispatcherInterface $eventDispatcher) {
        $this->clients = new \SplObjectStorage();
        $this->eventDispatcher = $eventDispatcher;
        $this->commandService = $commandService;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        $this->users[$conn->resourceId] = $conn;
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     * @throws \App\Exception\LackHandlerToCommandException
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
                $this->registryUser($msg, $from);

                break;

            /**
             * Users send messages.
             * Send to users or alert (RabbitMQ).
             */
            case 'message':
                if (!isset($msg['conversationId'], $msg['message'])) {
                    break;
                }

                $this->saveMessage($msg, $from);

                break;

            /**
             * Create a new Conversation
             * return conversationId, full name user and result.
             */
            case 'create':
                if (!isset($msg['receiveId'])) {
                    break;
                }

                $this->createConversation($msg, $from);

                break;
            default:
                break;
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @throws \App\Exception\LackHandlerToCommandException
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        unset($this->users[(int) $conn->resourceId]);

        $deleteOnlineUserCommand = new DeleteOnlineUserCommand((int) $conn->resourceId);

        $this->commandService->handle($deleteOnlineUserCommand);
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()} \n";

        $conn->close();
    }

    /**
     * @param array $msg
     * @param ConnectionInterface $from
     * @throws \App\Exception\LackHandlerToCommandException
     */
    private function registryUser(array $msg, ConnectionInterface $from): void
    {
        $registryOnlineUserListener = new RegistryOnlineUserEventListener();
        $this->eventDispatcher->addListener(
            RegistryOnlineUserEvent::NAME,
            [$registryOnlineUserListener, 'setResult']
        );

        $registryOnlineUserCommand = new RegistryOnlineUserCommand($msg, $from->resourceId);
        $this->commandService->handle($registryOnlineUserCommand);

        if (!$registryOnlineUserListener->isResult()) {
            $this->onClose($from);
        }
    }

    /**
     * @param array $msg
     * @param ConnectionInterface $from
     * @throws \App\Exception\LackHandlerToCommandException
     */
    private function saveMessage(array $msg, ConnectionInterface $from): void
    {
        $addMessageEventListener = new AddMessageEventListener();
        $addNotificationNewMessageListener = new AddNotificationNewMessageEventListener();
        $this->eventDispatcher->addListener(AddMessageEvent::NAME, [$addMessageEventListener, 'setSendUsers']);
        $this->eventDispatcher->addListener(AddNotificationNewMessageEvent::NAME, [$addNotificationNewMessageListener, 'setSend']);

        $addMessageCommand = new AddMessageCommand($msg, $from->resourceId);
        $this->commandService->handle($addMessageCommand);

        $usersConnIdAndSendNotification = $addMessageEventListener->getSendUsers();

        if (isset($usersConnIdAndSendNotification['notification'])) {
            $fromUserToken = htmlspecialchars($msg['userId']);

            /**
             * Send notification
             */
            foreach ($usersConnIdAndSendNotification['notification'] as $userToSendNotificationToken) {
                $addNotificationCommand = new AddNotificationNewMessageCommand($userToSendNotificationToken, $fromUserToken);
                $this->commandService->handle($addNotificationCommand);

                if ($addNotificationNewMessageListener->isSend()) {
                    break;
                }
            }

            /**
             * Delete notification
             */
            unset($usersConnIdAndSendNotification['notification']);
        }

        foreach ($usersConnIdAndSendNotification as $userConnId) {
            /**
             * Send message using WebSocket because user is online
             */
            $this->users[$userConnId]->send(json_encode([
                'type' => 'message',
                'conversationId' => htmlspecialchars($msg['conversationId']),
                'message' => htmlspecialchars($msg['message'])
            ]));
        }
    }

    /**
     * @param array $msg
     * @param ConnectionInterface $from
     * @throws \App\Exception\LackHandlerToCommandException
     */
    private function createConversation(array $msg, ConnectionInterface $from): void
    {
        $createConversationEvent = new CreateConversationEventListener();
        $this->eventDispatcher->addListener(
            CreateConversationEvent::NAME,
            [$createConversationEvent, 'setConversation']
        );

        $createConversationCommand = new CreateConversationCommand(
            htmlspecialchars($msg['userId']),
            htmlspecialchars($msg['receiveId'])
        );
        $this->commandService->handle($createConversationCommand);

        $result = $createConversationEvent->getConversation();

        if (!empty($result)) {
            $result['type'] = 'create';
        }

        $from->send(json_encode($result));
    }
}
