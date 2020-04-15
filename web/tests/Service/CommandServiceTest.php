<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Command\API\GetMessageCommand;
use App\Command\Web\CreateUserCommand;
use App\Entity\User;
use App\Exception\GetResultUndefinedException;
use App\Handler\API\GetMessageHandler;
use App\Handler\Web\CreateUserHandler;
use App\Service\CommandService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Psr\Container\ContainerInterface;

/**
 * Class CommandServiceTest
 */
class CommandServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testHandle(): void
    {
        $getMessageHandler = Mockery::mock(GetMessageHandler::class);
        $getMessageHandler->shouldReceive('handle')->once();

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->withArgs(['App\Handler\API\GetMessageHandler'])->andReturn($getMessageHandler);

        $command = new GetMessageCommand(1, 'conversationId', 1);

        $commandService = new CommandService($container);
        $commandService->handle($command);
    }
}
