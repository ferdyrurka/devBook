<?php
declare(strict_types=1);

namespace App\Tests\Console\DevMessenger;

use App\Console\DevMessenger\ServerDevMessenger;
use App\Service\DevMessengerService;
use App\Service\RedisService;
use Predis\Client;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use \Mockery;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerDevMessengerTest
 * @package App\Tests\Console\DevMessenger
 */
class ServerDevMessengerTest extends KernelTestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommandAndExecute(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('flushdb')->times(2);

        $redisService = Mockery::mock(RedisService::class);
        $redisService->shouldReceive('setDatabase')->times(2)->with(Mockery::on(function (int $key) {
            if ($key === 0 || $key === 1) {
                return true;
            }

            return false;
        }))->andReturn($client);

        $devMessengerService = Mockery::mock(DevMessengerService::class);

        // Execute tests

        $wsServer = Mockery::mock('overload:' . WsServer::class);
        $wsServer->shouldReceive('__construct')->once()->withArgs([DevMessengerService::class]);

        $httpServer = Mockery::mock('overload:' . HttpServer::class);
        $httpServer->shouldReceive('__construct')->once()->withArgs([WsServer::class]);

        $ioServer = Mockery::mock('overload:' . IoServer::class);
        $ioServer->shouldReceive('factory')->withArgs([HttpServer::class, '2013'])->once()->andReturn($ioServer);
        $ioServer->shouldReceive('run')->once();

        $serverDevMessenger = new ServerDevMessenger($devMessengerService, $redisService);

        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->once();
        $input = Mockery::mock(InputInterface::class);

        $serverDevMessenger->execute($input, $output);
    }
}
