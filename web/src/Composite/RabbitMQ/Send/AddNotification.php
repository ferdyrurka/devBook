<?php
declare(strict_types=1);

namespace App\Composite\RabbitMQ\Send;

use App\Composite\RabbitMQ\RabbitMQComponentAbstract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AddNotification
 * @package App\Composite\RabbitMQ\Send
 */
class AddNotification extends RabbitMQComponentAbstract
{
    /**
     * @var string
     */
    private $messageNotification;

    /**
     * @var int
     */
    private $userId;

    public function __construct(string $messageNotification, int $userId)
    {
        $this->messageNotification = $messageNotification;
        $this->userId = $userId;
    }

    public function execute(AMQPChannel $channel): void
    {
        $channel->queue_declare('notification', false, false, false);

        $message = new AMQPMessage(
            json_encode(
                [
                    'content' => $this->messageNotification,
                    'userId' => $this->userId,
                ]
            )
        );

        $channel->basic_publish($message, '', 'notification');
    }
}
