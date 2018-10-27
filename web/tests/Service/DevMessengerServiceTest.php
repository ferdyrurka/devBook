<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Command\Console\DevMessenger\DeleteOnlineUserCommand;
use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
use App\Service\CommandService;
use App\Service\DevMessengerService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Ratchet\ConnectionInterface;

/**
 * Class DevMessengerServiceTest
 * @package App\Tests\Service
 */
class DevMessengerServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $devMessengerService;
    private $commandService;
    private $createConversationCommand;
    private $registryOnlineUserCommand;
    private $deleteOnlineUserCommand;

    public function setUp(): void
    {
        $this->commandService = Mockery::mock(CommandService::class);
        $this->createConversationCommand = Mockery::mock(CreateConversationCommand::class);
        $this->registryOnlineUserCommand = Mockery::mock(RegistryOnlineUserCommand::class);
        $this->deleteOnlineUserCommand = Mockery::mock(DeleteOnlineUserCommand::class);

        $this->devMessengerService = new DevMessengerService(
            $this->commandService,
            $this->createConversationCommand,
            $this->registryOnlineUserCommand,
            $this->deleteOnlineUserCommand
        );

        parent::setUp();
    }

    public function testOnMessageRegistry(): void
    {
        #Delete user online

        $this->deleteOnlineUserCommand->shouldReceive('setConnId')->withArgs([1]);

        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->resourceId = 1;

        $this->registryOnlineUserCommand->shouldReceive('setConnId')->times(2)->withArgs([1]);
        $this->registryOnlineUserCommand->shouldReceive('setMessage')->with(Mockery::on(function (array $msg) {
            if (array_key_exists('userId', $msg)) {
                return true;
            }

            return false;
        }))->times(2);

        $this->commandService->shouldReceive('setCommand')->times(3)->with(Mockery::on(function ($class) {
            if ($class instanceof RegistryOnlineUserCommand || $class instanceof DeleteOnlineUserCommand) {
                return true;
            }

            return false;
        }));
        $this->commandService->shouldReceive('execute')->times(3);
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

    public function testOnMessageSendMessage(): void
    {
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
        }))->once();
        $conn->resourceId = 1;

        $this->devMessengerService->onOpen($conn);

        //Send message

        $this->commandService->shouldReceive('setCommand')->with(Mockery::on(function (AddMessageCommand $addMessageCommand) {
            $array = $addMessageCommand->getMessage();
            if ((array_key_exists('conversationId', $array) && array_key_exists('message', $array)) && $addMessageCommand->getFromId() === 1) {
                return true;
            }
            return true;
        }))->once();
        $this->commandService->shouldReceive('execute')->once();
        $this->commandService->shouldReceive('getResult')->once()->andReturn([1]);

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

        $this->createConversationCommand->shouldReceive('setReceiveUserToken')->once()->withArgs(['receiveIdValue']);
        $this->createConversationCommand->shouldReceive('setSendUserToken')->once()->withArgs(['userIdValue']);

        $this->commandService->shouldReceive('setCommand')->withArgs([CreateConversationCommand::class])->once();
        $this->commandService->shouldReceive('execute')->once();
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

    public function testOnClose()
    {
        $conn = Mockery::mock(ConnectionInterface::class);
        $conn->resourceId = 1;

        $this->deleteOnlineUserCommand->shouldReceive('setConnId')->once()->withArgs([1]);

        $this->commandService->shouldReceive('setCommand')->withArgs([DeleteOnlineUserCommand::class])->once();
        $this->commandService->shouldReceive('execute')->once();

        $this->devMessengerService->onClose($conn);
    }
}
