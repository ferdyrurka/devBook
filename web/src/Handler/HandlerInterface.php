<?php

namespace App\Handler;

use App\Command\CommandInterface;

/**
 * Interface CommandInterface
 * @package App\Command
 */
interface HandlerInterface
{
    public function handle(CommandInterface $command): void;
}
