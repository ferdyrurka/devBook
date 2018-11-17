<?php
declare(strict_types=1);

namespace App\Tests\Handler\Console\DevMessenger;

use App\Command\Console\DevMessenger\RegistryOnlineUserCommand;
use App\Entity\User;
use App\Event\RegistryOnlineUserEvent;
use App\Exception\UserNotFoundException;
use App\Handler\Console\DevMessenger\RegistryOnlineUserHandler;
use App\Repository\UserRepository;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class RegistryOnlineUserCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class RegistryOnlineUserHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var bool
     */
    private $result;

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

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->withArgs(function (string $name, $event) {
           if ($event instanceof RegistryOnlineUserEvent && $name === RegistryOnlineUserEvent::NAME) {
               $this->result = $event->isResult();
               return true;
           }

           return false;
        });

        $registryOnlineUserCommand = new RegistryOnlineUserCommand(['userId' => 'userIdValue'], 2);

        $registryOnlineUserHandler = new RegistryOnlineUserHandler($userRepository, $redisService, $eventDispatcher);

        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $this->assertTrue($this->result);

        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $this->assertFalse($this->result);

        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $this->assertFalse($this->result);
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

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->withArgs(function (string $name, $event) {
            if ($event instanceof RegistryOnlineUserEvent && $name === RegistryOnlineUserEvent::NAME) {
                $this->result = $event->isResult();
                return true;
            }

            return false;
        });

        $registryOnlineUserHandler = new RegistryOnlineUserHandler(
            $userRepository,
            $redisService,
            $eventDispatcher
        );
        $registryOnlineUserHandler->handle($registryOnlineUserCommand);
        $this->assertFalse($this->result);
    }
}
