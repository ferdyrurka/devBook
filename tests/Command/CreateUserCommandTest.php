<?php
declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class CreateUserCommandTest
 * @package App\Tests\Command
 */
class CreateUserCommandTest extends TestCase
{

    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var CreateUserCommand
     */
    private $createUserCommand;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function setUp(): void
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->createUserCommand = new CreateUserCommand($this->entityManager);

        parent::setUp();
    }

    public function testExecute(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('setCreatedAt')->withArgs([\DateTime::class])->once();
        $user->shouldReceive('setRoles')->withArgs(['ROLE_USER'])->once();
        $user->shouldReceive('setStatus')->withArgs([1])->once();

        $this->entityManager->shouldReceive('persist')->withArgs([User::class])->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->createUserCommand->setUser($user);

        $this->assertNull($this->createUserCommand->execute());
    }
}
