<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ;

use App\Console\RabbitMQ\Handler\AddNotificationHandler;
use App\Console\RabbitMQ\NotificationWorkerConsole;
use App\Service\RabbitMQConnectService;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationWorkerConsoleTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $addNotificationHandler = Mockery::mock(AddNotificationHandler::class);
        $addNotificationHandler->shouldReceive('handle');

        $amqpChannel = Mockery::mock(AMQPChannel::class);
        $amqpChannel->shouldReceive('queue_declare')->withArgs([
            'notification', false, false, false
        ])->once();
        $amqpChannel->shouldReceive('basic_consume')->withArgs([
            'notification', '', false, true, false, false, [$addNotificationHandler, 'handle']
        ])->once();
        $amqpChannel->shouldReceive('wait')->never();
        $amqpChannel->callbacks = [];

        $rabbitMQConnect = Mockery::mock(RabbitMQConnectService::class);
        $rabbitMQConnect->shouldReceive('getChannel')->once()->andReturn($amqpChannel);
        $rabbitMQConnect->shouldReceive('close')->once();

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->times(2);

        $input = Mockery::mock(InputInterface::class);

        $notificationWorkerConsole = new NotificationWorkerConsole($addNotificationHandler, $rabbitMQConnect);
        $notificationWorkerConsole->execute($input, $output);
    }
}
