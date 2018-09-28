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

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
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
