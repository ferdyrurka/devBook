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
     * @var string
     */
    private $userToken;

    public function __construct(string $messageNotification, string $userToken)
    {
        $this->messageNotification = $messageNotification;
        $this->userToken = $userToken;
    }

    public function execute(AMQPChannel $channel): void
    {
        $channel->queue_declare('notification', false, false, false);

        $message = new AMQPMessage(
            json_encode(
                [
                    'notificationMessage' => $this->messageNotification,
                    'userToken' => $this->userToken,
                ]
            )
        );

        $channel->basic_publish($message, '', 'notification');
    }
}

