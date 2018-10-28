<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ\Command;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQCommandAbstract
 * @package App\Console\Command\RabbitMQ
 */
abstract class RabbitMQHandlerAbstract
{
    /**
     * @param AMQPMessage $jsonMessage
     */
    abstract public function execute(AMQPMessage $jsonMessage): void;
}
