<?php
declare(strict_types=1);

namespace App\Tests\Handler\Console\DevMessenger;

use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Handler\Console\DevMessenger\RegistryOnlineUserHandler;
use App\Repository\UserRepository;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;

/**
 * Class RegistryOnlineUserCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class RegistryOnlineUserHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(1);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->andReturn($user)->times(3)
            ->withArgs(['userIdValue']);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('set')->times(3);
        $client->shouldReceive('exists')->times(5)
            ->with(Mockery::on(function ($key) {
                if ($key === 2 || $key === 'userIdValue') {
                    return true;
                }

                return false;
            }))->andReturn(
                0,
                0,
                #Failed exist in table users by connId
                1,
                #Failed exist in table users by Uuid
                0,
                1
            );

        $redisService = Mockery::mock(RedisService::class);
        $redisService->shouldReceive('setDatabase')->times(5)->with(Mockery::on(function (int $key) {
            if ($key === 1 || $key === 0) {
                return true;
            }

            return false;
        }))->andReturn($client);

        $registryOnlineUserCommand = new RegistryOnlineUserCommand(['userId' => 'userIdValue'], 2);

        $registryOnlineUserHandler = new RegistryOnlineUserHandler($userRepository, $redisService);

        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $result = $registryOnlineUserHandler->getResult();
        $this->assertTrue($result);

        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $result = $registryOnlineUserHandler->getResult();
        $this->assertFalse($result);

        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $result = $registryOnlineUserHandler->getResult();
        $this->assertFalse($result);
    }

    public function testUserNotFoundException(): void
    {
        $client = Mockery::mock(Client::class);

        $redisService = Mockery::mock(RedisService::class);
        $redisService->shouldReceive('setDatabase')->once()->with(Mockery::on(function (int $key) {
            if ($key === 0) {
                return true;
            }

            return false;
        }))->andReturn($client);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->once()->withArgs(['userIdValue'])
            ->andThrow(new UserNotFoundException());

        $registryOnlineUserCommand = new RegistryOnlineUserCommand(['userId' => 'userIdValue'], 2);

        $registryOnlineUserHandler = new RegistryOnlineUserHandler($userRepository, $redisService);
        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $result = $registryOnlineUserHandler->getResult();
        $this->assertFalse($result);
    }
}
