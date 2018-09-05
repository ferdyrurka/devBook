<?php
declare(strict_types=1);

namespace App\Command\Console;

use App\Command\CommandInterface;
use App\Exception\MessageIsEmptyException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;

/**
 * Class AddPostCommand
 * @package App\Command\Console
 */
class AddPostCommand implements CommandInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $message;

    /**
     * AddPostCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $message
     */
    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    /**
     * @throws MessageIsEmptyException
     */
    public function execute(): void
    {
        if (!isset($this->message['content']) || !isset($this->message['user'])) {
            throw new MessageIsEmptyException('Message is empty!');
        }

        $time = new \DateTime("now");
        $time->setTimezone(new \DateTimeZone("Europe/Warsaw"));

        $post = new Post();
        $post->setContent($this->message['content']);
        $post->setCreatedAt($time);
        $post->setUpdatedAt($time);
        $post->setUserRefrences($this->message['user']);

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
