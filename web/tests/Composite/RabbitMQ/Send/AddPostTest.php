<?php
declare(strict_types=1);

namespace App\Tests\Composite\RabbitMQ\Send;

use App\Composite\RabbitMQ\Send\AddPost;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class AddPostTest
 * @package App\Tests\Composite\RabbitMQ\Send
 */
class AddPostTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $addPost = new AddPost('Hello World', 1);

        $amqpChannel = \Mockery::mock(AMQPChannel::class);
        $amqpChannel->shouldReceive('queue_declare')->once()->withArgs(['post', false, false, false]);
        $amqpChannel->shouldReceive('basic_publish')->once()->withArgs([AMQPMessage::class, '', 'post']);

        $addPost->execute($amqpChannel);
    }
}
