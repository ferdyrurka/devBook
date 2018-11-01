<?php
declare(strict_types=1);

namespace App\Tests\Handler\Web;

use App\Command\Web\CreateUserCommand;
use App\Entity\User;
use App\Entity\UserToken;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\Web\CreateUserHandler;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateUserCommandTest
 * @package App\Tests\Command
 */
class CreateUserHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $userRepository = Mockery::mock(UserRepository::class);
        $userTokenRepository = Mockery::mock(UserTokenRepository::class);

        $passwordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->with(Mockery::on(function ($entity) {
            if ($entity instanceof UserToken || $entity instanceof User) {
                return true;
            }

            return false;
        }))->andReturn([], [], ['failed']);

        $createUserHandler = new CreateUserHandler(
            $userRepository,
            $userTokenRepository,
            $passwordEncoder,
            $validator
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('setCreatedAt')->withArgs([\DateTime::class])->times(2);
        $user->shouldReceive('setRoles')->withArgs(['ROLE_USER'])->times(2);
        $user->shouldReceive('setStatus')->withArgs([1])->times(2);
        $user->shouldReceive('setPassword')->times(2)->withArgs(['hash_password']);
        $user->shouldReceive('getPlainPassword')->times(2)->andReturn('qwertyuiop');
        $user->shouldReceive('setUserTokenReferences')->times(2)->withArgs([UserToken::class]);

        $passwordEncoder->shouldReceive('encodePassword')->times(2)
            ->withArgs([User::class, 'qwertyuiop'])->andreturn('hash_password')
        ;

        $userRepository->shouldReceive('save')->withArgs([User::class])->once();
        $userTokenRepository->shouldReceive('save')->withArgs([UserToken::class])->once();

        $createUserCommand = new CreateUserCommand($user);

        $createUserHandler->handle($createUserCommand);

        $this->expectException(ValidateEntityUnsuccessfulException::class);
        $createUserHandler->handle($createUserCommand);
    }
}
