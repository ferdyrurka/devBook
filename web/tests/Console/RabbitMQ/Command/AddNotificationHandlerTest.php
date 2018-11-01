<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ\Command;

use App\Console\RabbitMQ\Handler\AddNotificationHandler;
use App\Entity\Notification;
use App\Entity\User;
use App\Exception\NotFullMessageException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use \Mockery;

class AddNotificationHandlerTest extends TestCase
{

    public function testHandle(): void
    {
        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->withArgs([Notification::class])
            ->times(2)->andReturn([], ['failedValidate']);

        $notificationRepository = Mockery::mock(NotificationRepository::class);
        $notificationRepository->shouldReceive('save')->withArgs([Notification::class])->once();

        $user = Mockery::mock(User::class);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->times(2)->withArgs(['token'])->andReturn($user);

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode([
            'userToken' => 'token',
            'notificationMessage' => 'Value notification'
        ]);

        $addNotificationHandler = new AddNotificationHandler($validator, $notificationRepository, $userRepository);
        $addNotificationHandler->handle($amqpMessage);

        $this->expectException(ValidateEntityUnsuccessfulException::class);
        $addNotificationHandler->handle($amqpMessage);
    }

    public function testMessageNotFullException(): void
    {
        $validator = Mockery::mock(ValidatorInterface::class);
        $notificationRepository = Mockery::mock(NotificationRepository::class);
        $userRepository = Mockery::mock(UserRepository::class);

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode([
            'userId' => 1
        ]);

        $addNotificationHandler = new AddNotificationHandler($validator, $notificationRepository, $userRepository);
        $this->expectException(NotFullMessageException::class);
        $addNotificationHandler->handle($amqpMessage);
    }
}

