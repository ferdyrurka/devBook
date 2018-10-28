<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ;

use App\Console\RabbitMQ\PostWorkerConsole;
use App\Service\RabbitMQConnectService;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use Mockery;
use App\Console\RabbitMQ\Handler\AddPostHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PostWorkerConsoleTest
 */
class PostWorkerConsoleTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $addPostHandler = Mockery::mock(AddPostHandler::class);

        $amqpChannel = Mockery::mock(AMQPChannel::class);
        $amqpChannel->shouldReceive('queue_declare')->withArgs([
           'post', false, false, false
        ])->once();
        $amqpChannel->shouldReceive('basic_consume')->withArgs([
            'post', '', false, true, false, false, [$addPostHandler, 'handle']
        ])->once();
        $amqpChannel->shouldReceive('wait')->never();
        $amqpChannel->callbacks = [];


        $rabbitMQConnect = Mockery::mock(RabbitMQConnectService::class);
        $rabbitMQConnect->shouldReceive('getChannel')->once()->andReturn($amqpChannel);
        $rabbitMQConnect->shouldReceive('close')->once();

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->times(2);

        $input = Mockery::mock(InputInterface::class);

        $postWorkerConsole = new PostWorkerConsole($addPostHandler, $rabbitMQConnect);
        $postWorkerConsole->execute($input, $output);
    }
}
