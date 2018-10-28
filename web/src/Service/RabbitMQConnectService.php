<?php
declare(strict_types=1);

namespace App\Service;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class RabbitMQConnectService
 * @package App\Service
 */
class RabbitMQConnectService
{

    private $connection;

    /**
     * RabbitMQConnectedService constructor.
     */
    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('rabbitMQ', '5672', 'rabbitmq_admin', 'password');
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel(): AMQPChannel
    {
        return $this->connection->channel();
    }

    /**
     * @param AMQPChannel $channel
     */
    public function close(AMQPChannel $channel): void
    {
        $channel->close();
        $this->connection->close();
    }
}
