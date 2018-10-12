<?php
declare(strict_types=1);

namespace App\Tests\Console\DevMessenger;

use App\Console\DevMessenger\ServerDevMessenger;
use App\Service\DevMessengerService;
use App\Service\RedisService;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use \Mockery;

/**
 * Class ServerDevMessengerTest
 * @package App\Tests\Console\DevMessenger
 */
class ServerDevMessengerTest extends KernelTestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testCommand(): void
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

        $serverDevMessenger = new ServerDevMessenger($devMessengerService, $redisService);

        //No tests execute because it will start a webSocket server
    }
}
