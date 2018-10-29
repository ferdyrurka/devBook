<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Composite\RabbitMQ\Send\AddPost;
use App\Composite\RabbitMQ\SendComposite;
use App\Handler\HandlerInterface;

/**
 * Class AddPostToQueueCommand
 * @package App\Command\API
 */
class AddPostToQueueHandler implements HandlerInterface
{
    /**
     * @var SendComposite
     */
    private $sendComposite;

    /**
     * AddPostToQueueCommand constructor.
     * @param SendComposite $sendComposite
     */
    public function __construct(SendComposite $sendComposite)
    {
        $this->sendComposite = $sendComposite;
    }

    public function handle(CommandInterface $addPostToQueueCommand): void
    {
        $addPost = new AddPost(htmlspecialchars($addPostToQueueCommand->getContent()), (int) $addPostToQueueCommand->getUserId());

        $this->sendComposite->add($addPost);
        $this->sendComposite->run();
    }
}
