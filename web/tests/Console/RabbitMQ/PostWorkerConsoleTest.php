<?php
declare(strict_types=1);

namespace App\Tests\Console\RabbitMQ;

use App\Console\RabbitMQ\PostWorkerConsole;
use App\Service\RabbitMQConnectService;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use Mockery;
use App\Console\RabbitMQ\Command\AddPostCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PostWorkerConsoleTest
 */
class PostWorkerConsoleTest extends TestCase
{
    public function testExecute(): void
    {
        $addPostCommand = Mockery::mock(AddPostCommand::class);

        $amqpChannel = Mockery::mock(AMQPChannel::class);
        $amqpChannel->shouldReceive('queue_declare')->withArgs([
           'post', false, false, false
        ])->once();
        $amqpChannel->shouldReceive('basic_consume')->withArgs([
            'post', '', false, true, false, false, [AddPostCommand::class, 'execute']
        ])->once();
        $amqpChannel->shouldReceive('wait')->once();
        $amqpChannel->shouldReceive('close')->once();
        $amqpChannel->callbacks = [];


        $rabbitMQConnect = Mockery::mock(RabbitMQConnectService::class);
        $rabbitMQConnect->shouldReceive('getChannel')->once()->andReturn($amqpChannel);

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->times(2);

        $input = Mockery::mock(InputInterface::class);

        $postWorkerConsole = new PostWorkerConsole($addPostCommand, $rabbitMQConnect);
        $postWorkerConsole->execute($input, $output);
    }
}
