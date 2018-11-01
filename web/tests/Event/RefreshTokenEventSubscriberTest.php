<?php
declare(strict_types=1);

namespace App\Tests\Event;

use App\Entity\User;
use App\Entity\UserToken;
use App\Event\RefreshTokenEventSubscriber;
use App\Exception\ValidateEntityUnsuccessfulException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\Security\Core\Security;
use \DateTime;
use \DateTimeZone;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RefreshTokenEventListenerTest
 * @package App\Tests\Event
 */
class RefreshTokenEventSubscriberTest extends TestCase
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
            new DateTime('+1 hour'),
            new DateTime('-1 hour'),
            new DateTime('+1 hour'),
            new DateTime('+1 hour'),
            new DateTime('+1 hour')
        )->times(5);
        $userToken->shouldReceive('getRefreshWebToken')->andReturn(
            new DateTime('+1 hour'),
            new DateTime('+1 hour'),
            new DateTime('-1 hour'),
            new DateTime('+1 hour'),
            new DateTime('+1 hour')
        )->times(5);
        $userToken->shouldReceive('getRefreshMobileToken')->andReturn(
            new DateTime('+1 hour'),
            new DateTime('+1 hour'),
            new DateTime('+1 hour'),
            new DateTime('-1 hour'),
            new DateTime('-1 hour')
        )->times(5);

        $userToken->shouldReceive('setRefreshPublicToken')->once();
        $userToken->shouldReceive('setRefreshWebToken')->once();
        $userToken->shouldReceive('setRefreshMobileToken')->times(2);

        $userToken->shouldReceive('setPublicToken')->with(Mockery::on(function ($token) {
            return $this->validateToken($token);
        }))->once();
        $userToken->shouldReceive('setPrivateWebToken')->with(Mockery::on(function ($token) {
            return $this->validateToken($token);
        }))->once();
        $userToken->shouldReceive('setPrivateMobileToken')->with(Mockery::on(function ($token) {
            return $this->validateToken($token);
        }))->times(2);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getUserTokenReferences')->andReturn($userToken)->times(5);

        $security = Mockery::mock('overload:'.Security::class)->makePartial();
        $security->shouldReceive('getUser')->andReturn($user, $user, $user, $user, null, $user)->times(5);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->withArgs([UserToken::class])
            ->andReturn([], [], [], ['Validation false'])->times(4);

        $refreshToken = new RefreshTokenEventSubscriber($security, $entityManager, $validator);
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
        /**
         * Validate false
         */
        $this->expectException(ValidateEntityUnsuccessfulException::class);
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

