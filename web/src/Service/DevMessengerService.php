<?php
declare(strict_types=1);

namespace App\Service;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Command\Console\DevMessenger\AddNotificationNewMessageCommand;
use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Command\Console\DevMessenger\DeleteOnlineUserCommand;
use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
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
    private $users;

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * DevMessengerService constructor.
     * @param CommandService $commandService
     */
    public function __construct(CommandService $commandService) {
        $this->clients = new \SplObjectStorage();

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
     * @throws \App\Exception\GetResultUndefinedException
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
                $registryOnlineUserCommand = new RegistryOnlineUserCommand($msg, $from->resourceId);

                $this->commandService->handle($registryOnlineUserCommand);

                if ($this->commandService->getResult() === false) {
                    $this->onClose($from);
                }

                break;

            /**
             * Users send messages.
             * Send to users or alert (RabbitMQ).
             */
            case 'message':
                if (!array_key_exists('conversationId', $msg) || empty($msg['conversationId']) ||
                    !array_key_exists('message', $msg) || empty($msg['message'])
                ) {
                    break;
                }

                $addMessageCommand = new AddMessageCommand($msg, $from->resourceId);

                $this->commandService->handle($addMessageCommand);
                $usersConnIdAndSendNotification = $this->commandService->getResult();

                if (isset($usersConnIdAndSendNotification['notification'])) {
                    $fromUserToken = htmlspecialchars($msg['userId']);

                    /**
                     * Send notification
                     */
                    foreach ($usersConnIdAndSendNotification['notification'] as $userToSendNotificationToken) {
                        $addNotificationCommand = new AddNotificationNewMessageCommand($userToSendNotificationToken, $fromUserToken);
                        $this->commandService->handle($addNotificationCommand);
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

                break;

            /**
             * Create a new Conversation
             * return conversationId, full name user and result.
             */
            case 'create':
                if (!array_key_exists('receiveId', $msg) || empty($msg['receiveId'])) {
                    break;
                }

                $createConversationCommand = new CreateConversationCommand(
                    htmlspecialchars($msg['userId']),
                    htmlspecialchars($msg['receiveId'])
                );
                $this->commandService->handle($createConversationCommand);

                $result = $this->commandService->getResult();

                if (!empty($result)) {
                    $result['type'] = 'create';
                }

                $from->send(json_encode($result));

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
}
