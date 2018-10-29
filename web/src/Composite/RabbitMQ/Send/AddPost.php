<?php
declare(strict_types=1);

namespace App\Composite\RabbitMQ\Send;

use App\Composite\RabbitMQ\RabbitMQComponentAbstract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AddPost
 * @package App\Composite\RabbitMQ\Send
 */
class AddPost extends RabbitMQComponentAbstract
{
    public function __construct(string $content, int $userId)
    {
        $this->content = $content;
        $this->userId = $userId;
    }

    /**
     * @param AMQPChannel $channel
     */
    public function execute(AMQPChannel $channel): void
    {
        $channel->queue_declare('post', false, false, false);

        $message = new AMQPMessage(
            json_encode(
                [
                    'content' => $this->content,
                    'userId' => $this->userId,
                ]
            )
        );

        $channel->basic_publish($message, '', 'post');
    }
}
