<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;
use App\Composite\RabbitMQ\Send\AddPost;
use App\Composite\RabbitMQ\SendComposite;
use App\Entity\User;

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
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
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
        $addPost->setUser($this->user);
        $addPost->setContent($this->content);

        $sendComposite = new SendComposite();
        $sendComposite->add($addPost);

        $sendComposite->run();
    }
}
