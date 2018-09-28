<?php
declare(strict_types=1);

namespace App\Service;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Command\Console\DevMessenger\CreateConversationCommand;
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
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var CreateConversationCommand
     */
    private $createConversationCommand;

    /**
     * DevMessengerService constructor.
     * @param CommandService $commandService
     * @param AddMessageCommand $addMessageCommand
     * @param CreateConversationCommand $createConversationCommand
     * @param UserRepository $userRepository
     */
    public function __construct(
        CommandService $commandService,
        AddMessageCommand $addMessageCommand,
        CreateConversationCommand $createConversationCommand,
        UserRepository $userRepository
    ) {
        $this->clients = new \SplObjectStorage();

        $this->userRepository = $userRepository;
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
                if (array_key_exists('userId', $msg)) {
                    try {
                        $user = $this->userRepository->getOneByPrivateWebToken(
                            $msg['userId'] = htmlspecialchars($msg['userId'])
                        );
                    } catch (UserNotFoundException $exception) {
                        $this->onClose($from);
                        return;
                    }

                    $this->users[$msg['userId']]['conn'] = $from;
                    $this->users[$msg['userId']]['id'] = $user->getId();
                }

                return;
            /**
             * Users send messages.
             * Send to users or alert (RabbitMQ).
             */
            case 'message':
                if (array_key_exists(
                    $msg['conversationId'] = htmlspecialchars($msg['conversationId']),
                    $this->conversation
                )
                ) {
                    $conversation = $this->conversation[$msg['conversationId']];

                    if (!in_array($msg['userId'] = htmlspecialchars($msg['userId']), $conversation)) {
                        return;
                    }

                    $message = new Message();
                    $message->setMessage($msg['message'] = htmlspecialchars($msg['message']));
                    $message->setConversationId($msg['conversationId']);
                    $message->setSendUserId((int) $this->users[$msg['userId']]['id']);

                    $this->addMessageCommand->setMessage($message);

                    $this->commandService->setCommand($this->addMessageCommand);
                    $this->commandService->execute();

                    foreach ($conversation as $userId) {
                        //User is online
                        if ($userId !== $msg['userId'] && array_key_exists($userId, $this->users)) {
                            $this->users[$userId]['conn']->send(json_encode([
                                'type' => 'message',
                                'message' => $msg['message']
                            ]));
                        }
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
                    $this->conversation[$result['conversationId']] = $result['usersId'];

                    //This is private web tokens users.
                    unset($result['usersId']);
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
