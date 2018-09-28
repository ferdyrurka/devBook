<?php

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
        $this->connection = new AMQPStreamConnection('localhost', '5672', 'guest', 'guest');
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
    public function close(AMQPChannel $channel)
    {
        $channel->close();
        $this->connection->close();
    }
}
