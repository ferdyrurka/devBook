<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Command\Console\DevMessenger\AddNotificationNewMessageCommand;
use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Command\Console\DevMessenger\DeleteOnlineUserCommand;
use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
use App\Event\AddMessageEvent;
use App\EventListener\AddMessageEventListener;
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

    public function testOnMessageRegistry(): void
    {
        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->resourceId = 1;

        $this->commandService->shouldReceive('handle')->times(3)->with(Mockery::on(function ($class) {
            if (($class instanceof RegistryOnlineUserCommand && array_key_exists('userId', $class->getMessage()) && $class->getConnId() === 1) ||
                $class instanceof DeleteOnlineUserCommand
            ) {
                return true;
            }

            return false;
        }));
        $this->commandService->shouldReceive('getResult')->times(2)->andReturn(true, false);

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'registry',
            'userId' => 'userIdValue'
        ]));

        #Result is false

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'registry',
            'userId' => 'userIdValue'
        ]));
    }

    /**
     * @runInSeparateProcess
     */
    public function testOnMessageSendMessage(): void
    {
        $addMessageListener = Mockery::mock('overload:' . AddMessageEventListener::class);
        $addMessageListener->shouldReceive('getSendUsers')->once()->andReturn(
            [
                1,
                'notification' => [
                    0 => 'userTokenNotification',
                    1 => 'userTokenNotification'
                ]
            ]
        );

        $this->eventDispatcher->shouldReceive('addListener')->times(2)->withArgs(function (string $name, array $listener) {
            if ($listener[0] instanceof AddMessageEventListener && $name === AddMessageEvent::NAME) {
                return true;
            }

            return false;
        });

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
        }))->times(2);
        $conn->resourceId = 1;

        $this->devMessengerService->onOpen($conn);

        //Send message

        $this->commandService->shouldReceive('handle')->with(Mockery::on(function ($command) {

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
        }))->times(5);

        $this->commandService->shouldReceive('getResult')->andReturn(false, true);

        #Send only WebSocket

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'message',
            'userId' => 'userIdValue',
            'conversationId' => 'conversationIdValue',
            'message' => 'messageValue'
        ]));

        #Send notification and WebSocket
        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'message',
            'userId' => 'userIdValue',
            'conversationId' => 'conversationIdValue',
            'message' => 'messageValue'
        ]));

        #Not exist conversationId, break execute code

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'message',
            'userId' => 'userIdValue',
            'message' => 'messageValue'
        ]));

        #Not exist message, break execute code

        $this->devMessengerService->onMessage($conn, json_encode([
            'type' => 'message',
            'userId' => 'userIdValue',
            'conversationId' => 'conversationIdValue'
        ]));
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
        $this->commandService->shouldReceive('getResult')->once()->andReturn([
            'result' => true,
            'conversationId' => 'conversationIdValue',
            'fullName' => 'fullNameReceiveUser'
        ]);

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
