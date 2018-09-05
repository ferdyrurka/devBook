<?php
declare(strict_types=1);

namespace App\Tests\Composite\RabbitMQ\Send;

use App\Composite\RabbitMQ\Send\AddPost;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use App\Entity\User;

/**
 * Class AddPostTest
 * @package App\Tests\Composite\RabbitMQ\Send
 */
class AddPostTest extends TestCase
{
    public function testExecute(): void
    {
        $addPost = new AddPost();
        $addPost->setContent('Hello World');
        $addPost->setUser(new User());

        $amqpChannel = \Mockery::mock(AMQPChannel::class);
        $amqpChannel->shouldReceive('queue_declare')->once()->withArgs(['post', false, false, false]);
        $amqpChannel->shouldReceive('basic_publish')->once()->withArgs([AMQPMessage::class, '', 'post']);
    }
}
