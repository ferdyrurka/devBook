<?php
declare(strict_types=1);

namespace App\Composite\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class RabbitMQComponentAbstract
 * @package App\Composite\RabbitMQ
 */
abstract class RabbitMQComponentAbstract
{
    /**
     * @param AMQPChannel $channel
     */
    abstract public function execute(AMQPChannel $channel): void;
}
