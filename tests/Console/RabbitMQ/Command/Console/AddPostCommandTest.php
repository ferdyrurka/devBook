<?php
declare(strict_types=1);

namespace App\Tests\Command\Console;

use App\Command\Console\AddPostCommand;
use App\Exception\MessageIsEmptyException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use App\Entity\User;
use App\Entity\Post;

/**
 * Class AddPostCommandTest
 * @package App\Tests\Command\Console
 */
class AddPostCommandTest extends TestCase
{
    /**
     * @throws \App\Exception\MessageIsEmptyException
     */
    public function testExecute()
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->withArgs([Post::class])->once();
        $entityManager->shouldReceive('flush')->once();

        $addPostCommand = new AddPostCommand($entityManager);

        $addPostCommand->setMessage(['content' => '', 'user' => new User()]);

        $addPostCommand->execute();

        $addPostCommand->setMessage(['user' => new User()]);

        $this->expectException(MessageIsEmptyException::class);
        $addPostCommand->execute();
    }
}
