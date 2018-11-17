<?php
declare(strict_types=1);

namespace App\Tests\Service;

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
use App\Service\CommandService;
use App\Service\DevMessengerService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Ratchet\ConnectionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class DevMessengerServiceTest
 * @package App\Tests\Service
 */
class DevMessengerServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var DevMessengerService
     */
    private $devMessengerService;

    /**
     * @var CommandService
     */
    private $commandService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function setUp(): void
    {
        $this->commandService = Mockery::mock(CommandService::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->devMessengerService = new DevMessengerService($this->commandService, $this->eventDispatcher);

        parent::setUp();
    }

    /**
     * @param bool $registryResult
     * @param int $handleTimes
     * @throws \App\Exception\GetResultUndefinedException
     * @throws \App\Exception\LackHandlerToCommandException
     * @runInSeparateProcess
     * @dataProvider OnMessageRegistryData
     */
    public function testOnMessageRegistry(bool $registryResult, int $handleTimes): void
    {
        $this->eventDispatcher->shouldReceive('addListener')->withArgs(function (string $name, array $args) {
            if ($args[0] instanceof RegistryOnlineUserEventListener && $name === RegistryOnlineUserEvent::NAME) {
                return true;
            }

            return false;
        })->once();

        $registryOnlineUserEventListener = Mockery::mock('overload:' . RegistryOnlineUserEventListener::class);
        $registryOnlineUserEventListener->shouldReceive('isResult')->andReturn($registryResult);

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->resourceId = 1;

        $this->commandService->shouldReceive('handle')->times($handleTimes)->with(Mockery::on(function ($class) {
            if (($class instanceof RegistryOnlineUserCommand && array_key_exists('userId', $class->getMessage()) && $class->getConnId() === 1) ||
                $class instanceof DeleteOnlineUserCommand
            ) {
                return true;
            }

            return false;
        }));

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'registry',
            'userId' => 'userIdValue'
        ]));
    }

    public function OnMessageRegistryData(): array
    {
        return [
            #1
            [
                true,
                1
            ],
            #2
            [
                false,
                2
            ]
        ];
    }

    /**
     * @param array $onMessage
     * @param array $addMessage
     * @param bool $addNotification
     * @param array $times
     * @throws \App\Exception\GetResultUndefinedException
     * @throws \App\Exception\LackHandlerToCommandException
     * @runInSeparateProcess
     * @dataProvider onMessageSendMessageData
     */
    public function testOnMessageSendMessage(
        array $onMessage,
        array $addMessage,
        bool $addNotification,
        array $times
    ): void {
        // Listeners

        $addMessageListener = Mockery::mock('overload:' . AddMessageEventListener::class);
        $addMessageListener->shouldReceive('getSendUsers')->andReturn($addMessage)->times($times['getSendUsers']);

        $addNotificationListener = Mockery::mock('overload:' . AddNotificationNewMessageEventListener::class);
        $addNotificationListener->shouldReceive('isSend')->andReturn($addNotification)->times($times['isSend']);

        // Event

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('addListener')->withArgs(function (string $name, array $listener) {
            if (
                ($listener[0] instanceof AddMessageEventListener && $name === AddMessageEvent::NAME) ||
                (
                    $listener[0] instanceof AddNotificationNewMessageEventListener &&
                    $name === AddNotificationNewMessageEvent::NAME
                )
            ) {
                return true;
            }

            return false;
        })->times($times['addListener']);

        //Open connection

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->shouldReceive('send')->with(Mockery::on(function (string $jsonSend) {
            $jsonSend = json_decode($jsonSend, true);

            if (array_key_exists('type', $jsonSend) &&
                $jsonSend['type'] === 'message' &&
                array_key_exists('conversationId', $jsonSend) &&
                $jsonSend['conversationId'] === 'conversationIdValue' &&
                array_key_exists('message', $jsonSend) &&
                $jsonSend['message'] === 'messageValue'
            ) {
                return true;
            }

            return false;
        }))->times($times['send']);
        $conn->resourceId = 1;

        //Send message

        $commandService = Mockery::mock(CommandService::class);
        $commandService->shouldReceive('handle')->with(Mockery::on(function ($command) {

            if ($command instanceof AddMessageCommand) {
                $array = $command->getMessage();
                if ((
                        array_key_exists('conversationId', $array) &&
                        array_key_exists('message', $array)) && $command->getFromId() === 1
                ) {
                    return true;
                }
            } elseif ($command instanceof AddNotificationNewMessageCommand) {
                if ($command->getUserFromToken() === 'userIdValue' &&
                    $command->getUserToken() === 'userTokenNotification'
                ) {
                    return true;
                }
            }

            return false;
        }))->times($times['handle']);

        $devMessengerService = new DevMessengerService($commandService, $eventDispatcher);
        $devMessengerService->onOpen($conn);
        $devMessengerService->onMessage($conn, json_encode($onMessage));
    }

    public function onMessageSendMessageData(): array
    {
        return [
            #1
            [
                [
                    'type' => 'message',
                    'userId' => 'userIdValue',
                    'conversationId' => 'conversationIdValue',
                    'message' => 'messageValue'
                ],
                [1],
                false,
                [
                    'getSendUsers' => 1,
                    'isSend' => 0,
                    'addListener' => 2,
                    'send' => 1,
                    'handle' => 1,
                ]
            ],
            #2
            [
                [
                    'type' => 'message',
                    'userId' => 'userIdValue',
                    'conversationId' => 'conversationIdValue',
                    'message' => 'messageValue'
                ],
                [
                    1,
                    'notification' => [
                        0 => 'userTokenNotification'
                    ]
                ],
                true,
                [
                    'getSendUsers' => 1,
                    'isSend' => 1,
                    'addListener' => 2,
                    'send' => 1,
                    'handle' => 2,
                ]
            ],
            #3
            [
                [
                    'type' => 'message',
                    'userId' => 'userIdValue',
                    'conversationId' => 'conversationIdValue',
                    'message' => 'messageValue'
                ],
                [
                    1,
                    'notification' => [
                        0 => 'userTokenNotification'
                    ]
                ],
                false,
                [
                    'getSendUsers' => 1,
                    'isSend' => 1,
                    'addListener' => 2,
                    'send' => 1,
                    'handle' => 2,
                ]
            ],
            #4
            [
                [
                    'type' => 'message',
                    'userId' => 'userIdValue',
                    'message' => 'messageValue'
                ],
                [1],
                false,
                [
                    'getSendUsers' => 0,
                    'isSend' => 0,
                    'addListener' => 0,
                    'send' => 0,
                    'handle' => 0,
                ]
            ],
            #5
            [
                [
                    'type' => 'message',
                    'userId' => 'userIdValue',
                    'conversationId' => 'conversationIdValue'
                ],
                [1],
                false,
                [
                    'getSendUsers' => 0,
                    'isSend' => 0,
                    'addListener' => 0,
                    'send' => 0,
                    'handle' => 0,
                ]
            ]
        ];
    }

    public function testSendMessageCreate(): void
    {
        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->shouldReceive('send')->once()->with(Mockery::on(function (string $jsonSend) {
            $jsonSend = json_decode($jsonSend, true);

            if (array_key_exists('type', $jsonSend) &&
                $jsonSend['type'] === 'create' &&
                array_key_exists('conversationId', $jsonSend) &&
                array_key_exists('fullName', $jsonSend)
            ) {
                $this->assertTrue($jsonSend['result']);

                return true;
            }

            return false;
        }));

        $this->commandService->shouldReceive('handle')->with(Mockery::on(function (CreateConversationCommand $createConversationCommand) {
            if ($createConversationCommand->getReceiveUserToken() === 'receiveIdValue' &&
                $createConversationCommand->getSendUserToken() === 'userIdValue'
            ) {
                return true;
            }

            return false;
        }))->once();

        $createConversationListener = Mockery::mock('overload:' . CreateConversationEventListener::class);
        $createConversationListener->shouldReceive('getConversation')->once()->andReturn([
            'result' => true,
            'conversationId' => 'conversationIdValue',
            'fullName' => 'fullNameReceiveUser'
        ]);

        $this->eventDispatcher->shouldReceive('addListener')->withArgs(function (string $name,array $args) {
            if ($args[0] instanceof CreateConversationEventListener && $name === CreateConversationEvent::NAME) {
                return true;
            }

            return false;
        })->once();

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'create',
            'userId' => 'userIdValue',
            'receiveId' => 'receiveIdValue'
        ]));

        #not exist receive id

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'create',
            'userId' => 'userIdValue'
        ]));
    }

    public function testOnClose(): void
    {
        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->resourceId = 1;

        $this->commandService->shouldReceive('handle')->with(Mockery::on(function (DeleteOnlineUserCommand $deleteOnlineUserCommand) {
            if ($deleteOnlineUserCommand->getConnId() === 1) {
                return true;
            }

            return false;
        }))->once();

        $this->devMessengerService->onClose($conn);
    }
}
