<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\RedisService;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;

/**
 * Class RedisServiceTest
 * @package App\Tests\Service
 */
class RedisServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testService(): void
    {
        $client = Mockery::mock("overload:Predis\Client");
        $client->shouldReceive('__construct')->withArgs([[
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379
        ]])->once();
        $client->shouldReceive('auth')->once()->withArgs(['my-pass']);
        $client->shouldReceive('select')->withArgs([1])
            ->andReturn($client)->once();

        $redis = new RedisService();

        $this->assertInstanceOf(Client::class, $redis->setDatabase(1));

        $this->assertInstanceOf(Client::class, $redis->getClient());
    }
}
