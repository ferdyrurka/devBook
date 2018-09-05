<?php
declare(strict_types=1);

namespace App\Composite\RabbitMQ\Send;

use App\Composite\RabbitMQ\RabbitMQComponentAbstract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use App\Entity\User;

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
     * @var User
     */
    private $user;

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param AMQPChannel $channel
     */
    public function execute(AMQPChannel $channel): void
    {
        $channel->queue_declare('post', false, false, false);

        $message = new AMQPMessage([
            'content' => $this->content,
            'user' => $this->user,
        ]);

        $channel->basic_publish($message, '', 'post');
    }
}
