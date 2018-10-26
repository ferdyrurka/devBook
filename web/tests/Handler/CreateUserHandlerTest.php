<?php
declare(strict_types=1);

namespace App\Tests\Handler;

use App\Command\CreateUserCommand;
use App\Entity\User;
use App\Entity\UserToken;
use App\Handler\CreateUserHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class CreateUserCommandTest
 * @package App\Tests\Command
 */
class CreateUserHandlerTest extends TestCase
{

    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var CreateUserHandler
     */
    private $createUserHandler;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function setUp(): void
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->passwordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $this->createUserHandler = new CreateUserHandler($this->entityManager, $this->passwordEncoder);

        parent::setUp();
    }

    public function testExecute(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('setCreatedAt')->withArgs([\DateTime::class])->once();
        $user->shouldReceive('setRoles')->withArgs(['ROLE_USER'])->once();
        $user->shouldReceive('setStatus')->withArgs([1])->once();
        $user->shouldReceive('setPassword')->once()->withArgs(['hash_password']);
        $user->shouldReceive('getPlainPassword')->once()->andReturn('qwertyuiop');
        $user->shouldReceive('setUserTokenReferences')->once()->withArgs([UserToken::class]);

        $this->passwordEncoder
            ->shouldReceive('encodePassword')
            ->once()
            ->withArgs([User::class, 'qwertyuiop'])
            ->andreturn('hash_password')
        ;

        $this->entityManager->shouldReceive('persist')->with(Mockery::on(function ($obj) {
            if ($obj instanceof User || $obj instanceof UserToken) {
                return true;
            }

            return false;
        }))->times(2);
        $this->entityManager->shouldReceive('flush')->once();

        $createUserCommand = new CreateUserCommand($user);

        $this->createUserHandler->handle($createUserCommand);
    }
}
