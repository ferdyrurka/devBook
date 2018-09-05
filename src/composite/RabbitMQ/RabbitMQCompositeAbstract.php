<?php
declare(strict_types=1);

namespace App\Composite\RabbitMQ;

use App\Service\RabbitMQConnectService;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * Class RabbitMQCompositeAbstract
 * @package App\Composite\RabbitMQ
 */
abstract class RabbitMQCompositeAbstract extends RabbitMQComponentAbstract
{

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var RabbitMQConnectService
     */
    private $service;

    /**
     * RabbitMQCompositeAbstract constructor.
     */
    public function __construct()
    {
        $this->service = new RabbitMQConnectService();
        $this->channel = $this->connected();
    }

    /**
     * @return AMQPChannel
     */
    private function connected(): AMQPChannel
    {
        return $this->service->getChannel();
    }

    protected function close(): void
    {
        $this->service->close($this->channel);
    }

    /**
     * @param AMQPChannel $channel
     */
    public function execute(AMQPChannel $channel): void
    {
        return;
    }

    abstract public function run(): void;

    /**
     * @param RabbitMQComponentAbstract $componentAbstract
     * @return RabbitMQCompositeAbstract
     */
    abstract public function add(RabbitMQComponentAbstract $componentAbstract): RabbitMQCompositeAbstract;
}
