<?php
declare(strict_types=1);

namespace App\Tests\Composite\RabbitMQ\Send;

use App\Composite\RabbitMQ\Send\AddNotification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class AddNotificationTest
 * @package App\Tests\Composite\RabbitMQ\Send
 */
class AddNotificationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $addPost = new AddNotification('Hello World Message', 1);

        $amqpChannel = \Mockery::mock(AMQPChannel::class);
        $amqpChannel->shouldReceive('queue_declare')->once()->withArgs(['notification', false, false, false]);
        $amqpChannel->shouldReceive('basic_publish')->once()->withArgs([AMQPMessage::class, '', 'notification']);

        $addPost->execute($amqpChannel);
    }
}
