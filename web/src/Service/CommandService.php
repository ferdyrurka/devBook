<?php
declare(strict_types=1);

namespace App\Service;

use App\Command\CommandInterface;
use App\Exception\GetResultUndefinedException;
use App\Exception\LackHandlerToCommandException;
use App\Handler\HandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class CommandService
 * @package App\Service
 */
class CommandService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var HandlerInterface
     */
    private $handler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $commandClass
     * @return HandlerInterface
     * @throws LackHandlerToCommandException
     */
    private function getHandler(string $commandClass): HandlerInterface
    {
        $handlerClass = str_replace('Command', 'Handler', $commandClass);

        if (!\class_exists($commandClass)) {
            throw new LackHandlerToCommandException('Not found Handler from command: ' . $commandClass);
        }

        return $this->container->get($handlerClass);
    }

    /**
     * @param CommandInterface $command
     * @throws LackHandlerToCommandException
     */
    public function handle(CommandInterface $command): void
    {
        $this->handler = $this->getHandler(\get_class($command));

        $this->handler->handle($command);
    }

    /**
     * @return mixed
     * @throws GetResultUndefinedException
     */
    public function getResult()
    {
        if (!\method_exists($this->handler, 'getResult')) {
            throw new GetResultUndefinedException('Undefined method getResult in handler: ' . \get_class($this->handler));
        }

        return $this->handler->getResult();
    }
}
