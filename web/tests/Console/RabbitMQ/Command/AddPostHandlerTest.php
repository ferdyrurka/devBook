<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ\Command;

use App\Console\RabbitMQ\Handler\AddPostHandler;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Exception\MessageIsEmptyException;
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
     * @throws ValidateEntityUnsuccessfulException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testExecute(): void
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneById')->once()->withArgs([1]);

        $postRepository = Mockery::mock(PostRepository::class);
        $postRepository->shouldReceive('save')->withArgs([Post::class])->once();

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode(['content' => 'Hello World', 'userId' => 1]);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->once()->andReturn([]);

        $addPostCommand = new AddPostHandler($postRepository, $userRepository, $validator);

        $addPostCommand->handle($amqpMessage);

        $amqpMessage->body = json_encode(['userId' => 1]);

        $this->expectException(MessageIsEmptyException::class);
        $addPostCommand->handle($amqpMessage);
    }

    /**
     * @throws MessageIsEmptyException
     * @throws ValidateEntityUnsuccessfulException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testValidateEntityUnsuccessfulException(): void
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneById')->once()->withArgs([1]);

        $postRepository = Mockery::mock(PostRepository::class);

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode(['content' => 'Hello World', 'userId' => 1]);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->once()->andReturn(['failed']);

        $addPostCommand = new AddPostHandler($postRepository, $userRepository, $validator);

        $this->expectException(ValidateEntityUnsuccessfulException::class);
        $addPostCommand->handle($amqpMessage);
    }
}
