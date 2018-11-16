<?php
declare(strict_types=1);

namespace App\Tests\Handler\Console\DevMessenger;

use App\Command\Console\DevMessenger\AddNotificationNewMessageCommand;
use App\Composite\RabbitMQ\Send\AddNotification;
use App\Composite\RabbitMQ\SendComposite;
use App\Entity\User;
use App\Entity\UserToken;
use App\Event\AddNotificationNewMessageEvent;
use App\Handler\Console\DevMessenger\AddNotificationNewMessageHandler;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AddNotificationNewMessageHandlerTest
 * @package App\Tests\Handler\Console\DevMessenger
 */
class AddNotificationNewMessageHandlerTest extends TestCase
{

    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var array
     */
    private $result;

    /**
     * @runInSeparateProcess
     */
    public function testHandleGood(): void
    {
        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getPrivateMobileToken')->once()->andReturn('OtherUser');
        $userToken->shouldReceive('getPrivateWebToken')->once()->andReturn('OtherUser');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getUserTokenReferences')->once()->andReturn($userToken);
        $user->shouldReceive('getFirstName')->once()->andReturn('FirstName');
        $user->shouldReceive('getSurname')->once()->andReturn('Surname');

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->withArgs(['fromUserToken'])->andReturn($user);

        $sendComposite = Mockery::mock('overload:' . SendComposite::class);
        $sendComposite->shouldReceive('add')->withArgs([AddNotification::class])->once();
        $sendComposite->shouldReceive('run')->once();

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->once()->withArgs(
            function (string $name, $event) {
                if ($event instanceof AddNotificationNewMessageEvent &&
                    $name === AddNotificationNewMessageEvent::NAME
                ) {
                    $this->result = $event->isSend();
                    return true;
                }

                return false;
            }
        );

        $addNotificationNewMessageCommand = new AddNotificationNewMessageCommand('userToken', 'fromUserToken');

        $addNotificationNewMessageHandler = new AddNotificationNewMessageHandler($userRepository, $eventDispatcher);
        $addNotificationNewMessageHandler->handle($addNotificationNewMessageCommand);
        $this->assertTrue($this->result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testHandleUserSend(): void
    {
        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getPrivateMobileToken')->once()->andReturn('userToken');
        $userToken->shouldReceive('getPrivateWebToken')->once()->andReturn('OtherUser');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getUserTokenReferences')->once()->andReturn($userToken);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->withArgs(['fromUserToken'])->andReturn($user);

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->once()->withArgs(
            function (string $name, $event) {
                if ($event instanceof AddNotificationNewMessageEvent &&
                    $name === AddNotificationNewMessageEvent::NAME
                ) {
                    $this->result = $event->isSend();
                    return true;
                }

                return false;
            }
        );

        $addNotificationNewMessageCommand = new AddNotificationNewMessageCommand('userToken', 'fromUserToken');

        $addNotificationNewMessageHandler = new AddNotificationNewMessageHandler($userRepository, $eventDispatcher);
        $addNotificationNewMessageHandler->handle($addNotificationNewMessageCommand);
        $this->assertFalse($this->result);
    }
}

