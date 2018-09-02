<?php
declare(strict_types=1);

namespace App\Service;

use App\Command\CommandInterface;

/**
 * Class CommandService
 * @package App\Service
 */
class CommandService
{
    /**
     * @var CommandInterface
     */
    private $command;

    /**
     * @param CommandInterface $command
     * @return self
     */
    public function setCommand(CommandInterface $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function execute(): void
    {
        $this->command->execute();
    }
}
