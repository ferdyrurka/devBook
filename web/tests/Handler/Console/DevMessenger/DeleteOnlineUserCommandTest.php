<?php
declare(strict_types=1);

namespace App\Tests\Command\Console\DevMessenger;

use App\Command\Console\DevMessenger\DeleteOnlineUserCommand;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;

/**
 * Class DeleteOnlineUserCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class DeleteOnlineUserCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('get')->times(3)->with(Mockery::on(function ($key) {
            if ($key === 10 || $key === 'userUUIDToken') {
                return true;
            }

            return false;
        }))->andReturn('userUUIDToken', 'userUUIDToken', null);
        $client->shouldReceive('exists')->times(2)->andReturn(1, 0)->withArgs(['userUUIDToken']);
        $client->shouldReceive('del')->times(3)->with(Mockery::on(function (array $del) {
            if ($del[0] === 'userUUIDToken' || $del[0] === 10) {
                return true;
            }
            return false;
        }));

        $redisService = Mockery::mock(RedisService::class);
        $redisService->shouldReceive('setDatabase')
            ->with(Mockery::on(function (int $databaseId) {
                if ($databaseId === 0 || $databaseId === 1) {
                    return true;
                }
                return false;
            }))->times(5)->andReturn($client)
        ;

        $deleteOnlineUserCommand = new DeleteOnlineUserCommand($redisService);
        $deleteOnlineUserCommand->setConnId(10);
        #Okej success
        $deleteOnlineUserCommand->execute();

        #Not exist this user in userByUUID (1)
        $deleteOnlineUserCommand->execute();

        #Not exist in byConn
        $deleteOnlineUserCommand->execute();
    }
}
