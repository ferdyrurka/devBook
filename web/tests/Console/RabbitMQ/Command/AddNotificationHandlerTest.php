<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ\Command;

use App\Console\RabbitMQ\Handler\AddNotificationHandler;
use App\Entity\Notification;
use App\Entity\User;
use App\Exception\NotFullMessageException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->withArgs([Notification::class])->once();
        $entityManager->shouldReceive('flush')->once();

        $user = Mockery::mock(User::class);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->times(2)->withArgs(['token'])->andReturn($user);

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode([
            'userToken' => 'token',
            'notificationMessage' => 'Value notification'
        ]);

        $addNotificationHandler = new AddNotificationHandler($validator, $entityManager, $userRepository);
        $addNotificationHandler->handle($amqpMessage);

        $this->expectException(ValidateEntityUnsuccessfulException::class);
        $addNotificationHandler->handle($amqpMessage);
    }

    public function testMessageNotFullException(): void
    {
        $validator = Mockery::mock(ValidatorInterface::class);
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $userRepository = Mockery::mock(UserRepository::class);

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode([
            'userId' => 1
        ]);

        $addNotificationHandler = new AddNotificationHandler($validator, $entityManager, $userRepository);
        $this->expectException(NotFullMessageException::class);
        $addNotificationHandler->handle($amqpMessage);
    }
}

