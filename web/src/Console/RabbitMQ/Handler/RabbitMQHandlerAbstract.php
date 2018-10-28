<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ\Handler;

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
    abstract public function handle(AMQPMessage $jsonMessage): void;
}
