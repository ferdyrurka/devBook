<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Command\CommandInterface;
use App\Service\CommandService;
use PHPUnit\Framework\TestCase;

/**
 * Class CommandServiceTest
 */
class CommandServiceTest extends TestCase
{

    private $commandService;

    public function setUp()
    {
        $this->commandService = new CommandService();
        parent::setUp();
    }

    public function testExecute()
    {
        $command = \Mockery::mock(CommandInterface::class);
        $command->shouldReceive('execute')->once();

        $this->assertInstanceOf(CommandService::class, $this->commandService->setCommand($command));
        $this->assertNull($this->commandService->execute());
    }
}
