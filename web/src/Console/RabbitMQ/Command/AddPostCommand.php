<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ\Command;

use App\Entity\User;
use App\Exception\MessageIsEmptyException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AddPostCommand
 * @package App\Command\Console
 */
class AddPostCommand extends RabbitMQCommandAbstract
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * AddPostCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @param AMQPMessage $message
     * @throws MessageIsEmptyException
     */
    public function execute(AMQPMessage $message): void
    {
        $message = json_decode($message->body, true);

        if (!isset($message['content']) || !isset($message['userId'])) {
            throw new MessageIsEmptyException('Message is empty!');
        }

        $user = $this->userRepository->getOneById((int) $message['userId']);

        $time = new \DateTime("now");
        $time->setTimezone(new \DateTimeZone("Europe/Warsaw"));

        $post = new Post();
        $post->setContent((string) $message['content']);
        $post->setCreatedAt($time);
        $post->setUpdatedAt($time);
        $post->setUserReferences($user);

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
