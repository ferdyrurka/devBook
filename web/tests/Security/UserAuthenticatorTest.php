<?php
declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserAuthenticator;
use \Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserAuthenticatorTest
 * @package App\Tests\Security
 */
class UserAuthenticatorTest extends TestCase
{
    private $userAuthenticator;

    public function setUp()
    {
        $this->userAuthenticator = new UserAuthenticator();

        parent::setUp();
    }

    public function testGetUser(): void
    {
        $credentials = null;
        $userProvider = Mockery::mock(UserProviderInterface::class);

        $this->assertEmpty($this->userAuthenticator->getUser($credentials, $userProvider));

        $credentials = ['token' => 'tokenValue'];
        $userProvider->shouldReceive('loadUserByUsername')->once()
            ->withArgs(['tokenValue'])->andReturn(Mockery::mock(User::class));

        $this->assertInstanceOf(User::class, $this->userAuthenticator->getUser($credentials, $userProvider));
    }

    public function testCheckCredentials(): void
    {
        $this->assertTrue($this->userAuthenticator->checkCredentials([], Mockery::mock(UserInterface::class)));
    }

    public function testOnAuthenticationFailure(): void
    {
        $exception = Mockery::mock(AuthenticationException::class);
        $exception->shouldReceive('getMessageKey')->andReturn('An authentication exception occurred.')->once();
        $exception->shouldReceive('getMessageData')->andReturn([])->once();

        $this->assertInstanceOf(JsonResponse::class, $this->userAuthenticator->onAuthenticationFailure(
            Mockery::mock(Request::class),
            $exception
        ));
    }

    public function testStart(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('getRequestUri')->andReturn('/', '/api/create-conversation')->times(2);

        $authException = Mockery::mock(AuthenticationException::class);

        $this->assertInstanceOf(RedirectResponse::class, $this->userAuthenticator->start($request, $authException));

        $this->assertInstanceOf(Response::class, $this->userAuthenticator->start($request, $authException));
    }

    public function testOnAuthenticationSuccess(): void
    {
        $this->assertNull($this->userAuthenticator->onAuthenticationSuccess(
            Mockery::mock(Request::class),
            Mockery::mock(TokenInterface::class),
            'providerKey'
        ));
    }

    public function testSupportsRememberMe(): void
    {
        $this->assertFalse($this->userAuthenticator->supportsRememberMe());
    }
}

