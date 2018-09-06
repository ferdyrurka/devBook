<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;
use App\Composite\RabbitMQ\Send\AddPost;
use App\Composite\RabbitMQ\SendComposite;

/**
 * Class AddPostToQueueCommand
 * @package App\Command\API
 */
class AddPostToQueueCommand implements CommandInterface
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
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function execute(): void
    {
        $addPost = new AddPost();
        $addPost->setUserId($this->userId);
        $addPost->setContent(htmlspecialchars($this->content));

        $sendComposite = new SendComposite();
        $sendComposite->add($addPost);

        $sendComposite->run();
    }
}
