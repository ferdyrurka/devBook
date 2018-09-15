<?php
declare(strict_types=1);

namespace App\Tests\Command\Console\DevMessenger;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class AddMessageCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class AddMessageCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute()
    {
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->once()->withArgs([Message::class]);
        $entityManager->shouldReceive('flush')->once();

        $message = Mockery::mock(Message::class);
        $message->shouldReceive('setSendTime')->once();

        $addMessage = new AddMessageCommand($entityManager);
        $addMessage->setMessage($message);
        $this->assertNull($addMessage->execute());
    }
}
