<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ\Command;

use App\Console\RabbitMQ\Handler\AddPostHandler;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\UserRepository;
use App\Exception\MessageIsEmptyException;
use Doctrine\ORM\EntityManagerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use App\Entity\Post;
use \Mockery;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddPostCommandTest
 * @package App\Tests\Command\Console
 */
class AddPostHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws MessageIsEmptyException
     * @throws \App\Exception\ValidateEntityUnsuccessfulException
     */
    public function testExecute(): void
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneById')->once()->withArgs([1]);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->withArgs([Post::class])->once();
        $entityManager->shouldReceive('flush')->once();

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode(['content' => 'Hello World', 'userId' => 1]);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->once()->andReturn([]);

        $addPostCommand = new AddPostHandler($entityManager, $userRepository, $validator);

        $addPostCommand->handle($amqpMessage);

        $amqpMessage->body = json_encode(['userId' => 1]);

        $this->expectException(MessageIsEmptyException::class);
        $addPostCommand->handle($amqpMessage);
    }

    public function testValidateEntityUnsuccessfulException(): void
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneById')->once()->withArgs([1]);

        $entityManager = Mockery::mock(EntityManagerInterface::class);

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode(['content' => 'Hello World', 'userId' => 1]);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->once()->andReturn(['failed']);

        $addPostCommand = new AddPostHandler($entityManager, $userRepository, $validator);

        $this->expectException(ValidateEntityUnsuccessfulException::class);
        $addPostCommand->handle($amqpMessage);
    }
}
