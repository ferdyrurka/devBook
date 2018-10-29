<?php
declare(strict_types=1);

namespace App\Tests\Handler\Console\DevMessenger;

use App\Command\Console\DevMessenger\AddNotificationNewMessageCommand;
use App\Composite\RabbitMQ\Send\AddNotification;
use App\Composite\RabbitMQ\SendComposite;
use App\Entity\User;
use App\Entity\UserToken;
use App\Handler\Console\DevMessenger\AddNotificationNewMessageHandler;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;

class AddNotificationNewMessageHandlerTest extends TestCase
{

    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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

        $addNotificationNewMessageCommand = new AddNotificationNewMessageCommand('userToken', 'fromUserToken');

        $addNotificationNewMessageHandler = new AddNotificationNewMessageHandler($userRepository);
        $addNotificationNewMessageHandler->handle($addNotificationNewMessageCommand);
    }

    public function testHandleUserSend(): void
    {
        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getPrivateMobileToken')->once()->andReturn('userToken');
        $userToken->shouldReceive('getPrivateWebToken')->once()->andReturn('OtherUser');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getUserTokenReferences')->once()->andReturn($userToken);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->withArgs(['fromUserToken'])->andReturn($user);

        $addNotificationNewMessageCommand = new AddNotificationNewMessageCommand('userToken', 'fromUserToken');

        $addNotificationNewMessageHandler = new AddNotificationNewMessageHandler($userRepository);
        $addNotificationNewMessageHandler->handle($addNotificationNewMessageCommand);
    }
}

