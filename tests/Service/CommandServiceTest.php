<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Command\API\GetMessageCommand;
use App\Command\CommandInterface;
use App\Service\CommandService;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class CommandServiceTest
 */
class CommandServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $commandService;

    public function setUp(): void
    {
        $this->commandService = new CommandService();
        parent::setUp();
    }

    public function testExecute(): void
    {
        $command = Mockery::mock(CommandInterface::class);
        $command->shouldReceive('execute')->once();

        $this->assertInstanceOf(CommandService::class, $this->commandService->setCommand($command));
        $this->assertNull($this->commandService->execute());
    }

    public function testGetResult(): void
    {
        $command = Mockery::mock(GetMessageCommand::class);
        $command->shouldReceive('getResult')->once()->andReturn([0 => 'Result']);

        $this->assertInstanceOf(CommandService::class, $this->commandService->setCommand($command));
        $result = $this->commandService->getResult();
        $this->assertNotEmpty($result);
        $this->assertEquals('Result', $result[0]);
    }
}
