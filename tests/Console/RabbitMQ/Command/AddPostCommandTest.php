<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ\Command;

use App\Console\RabbitMQ\Command\AddPostCommand;
use App\Repository\UserRepository;
use App\Exception\MessageIsEmptyException;
use Doctrine\ORM\EntityManagerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use App\Entity\Post;
use \Mockery;

/**
 * Class AddPostCommandTest
 * @package App\Tests\Command\Console
 */
class AddPostCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws \App\Exception\MessageIsEmptyException
     */
    public function testExecute()
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneById')->once()->withArgs([1]);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->withArgs([Post::class])->once();
        $entityManager->shouldReceive('flush')->once();

        $amqpMessage = Mockery::mock(AMQPMessage::class);
        $amqpMessage->body = json_encode(['content' => 'Hello World', 'userId' => 1]);

        $addPostCommand = new AddPostCommand($entityManager, $userRepository);

        $addPostCommand->execute($amqpMessage);

        $amqpMessage->body = json_encode(['userId' => 1]);

        $this->expectException(MessageIsEmptyException::class);
        $addPostCommand->execute($amqpMessage);
    }
}
