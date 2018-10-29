<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\RabbitMQConnectService;
use \Mockery;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;

/**
 * Class RabbitMQConnectServiceTest
 * @package App\Tests\Service
 * @runTestsInSeparateProcesses
 */
class RabbitMQConnectServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testService(): void
    {
        $AMQPChannel = Mockery::mock(AMQPChannel::class);
        $AMQPChannel->shouldReceive('close')->once();

        $AMQPStreamConnection = Mockery::mock('overload:' . AMQPStreamConnection::class);
        $AMQPStreamConnection->shouldReceive('__construct')->withArgs([
            'rabbitMQ', '5672', 'rabbitmq_admin', 'password'
        ])->once();
        $AMQPStreamConnection->shouldReceive('channel')->once()->andReturn($AMQPChannel);
        $AMQPStreamConnection->shouldReceive('close')->once();

        $rabbitMQConnectService = new RabbitMQConnectService();

        $this->assertInstanceOf(AMQPChannel::class, $rabbitMQConnectService->getChannel());
        $rabbitMQConnectService->close($AMQPChannel);
    }
}
