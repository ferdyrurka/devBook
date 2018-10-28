<?php
declare(strict_types=1);

namespace App\Tests\Event;

use App\Entity\User;
use App\Entity\UserToken;
use App\Event\RefreshTokenEventListener;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\Security\Core\Security;
use \DateTime;
use \DateTimeZone;

/**
 * Class RefreshTokenEventListenerTest
 * @package App\Tests\Event
 */
class RefreshTokenEventListenerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws \Exception
     * @runInSeparateProcess
     */
    public function testOnKernelController(): void
    {
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->withArgs([UserToken::class])->times(3);
        $entityManager->shouldReceive('flush');

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getRefreshPublicToken')->andReturn(
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('-1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw'))
        )->times(4);
        $userToken->shouldReceive('getRefreshWebToken')->andReturn(
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('-1 hour', new DateTimeZone('Europe/Warsaw')),
             new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw'))
        )->times(4);
        $userToken->shouldReceive('getRefreshMobileToken')->andReturn(
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('+1 hour', new DateTimeZone('Europe/Warsaw')),
            new DateTime('-1 hour', new DateTimeZone('Europe/Warsaw'))
        )->times(4);

        $userToken->shouldReceive('setRefreshPublicToken')->once();
        $userToken->shouldReceive('setRefreshWebToken')->once();
        $userToken->shouldReceive('setRefreshMobileToken')->once();

        $userToken->shouldReceive('setPublicToken')->with(Mockery::on(function ($token) {
            return $this->validateToken($token);
        }))->once();
        $userToken->shouldReceive('setPrivateWebToken')->with(Mockery::on(function ($token) {
            return $this->validateToken($token);
        }))->once();
        $userToken->shouldReceive('setPrivateMobileToken')->with(Mockery::on(function ($token) {
            return $this->validateToken($token);
        }))->once();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getUserTokenReferences')->andReturn($userToken)->times(4);

        $security = Mockery::mock('overload:'.Security::class)->makePartial();
        $security->shouldReceive('getUser')->andReturn($user, $user, $user, $user, null)->times(5);

        $refreshToken = new RefreshTokenEventListener($security, $entityManager);
        /**
         * Tests times
         */
        $refreshToken->onKernelController();
        $refreshToken->onKernelController();
        $refreshToken->onKernelController();
        $refreshToken->onKernelController();
        /**
         * Tests user not logged
         */
        $refreshToken->onKernelController();
    }

    public function validateToken(string $token): bool
    {
        if (\strlen($token) === 36) {
            return true;
        }

        return false;
    }
}

