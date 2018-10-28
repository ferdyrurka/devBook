<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ\Handler;

use App\Exception\MessageIsEmptyException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddPostCommand
 * @package App\Command\Console
 */
class AddPostHandler extends RabbitMQHandlerAbstract
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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * AddPostCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    /**
     * @param AMQPMessage $message
     * @throws MessageIsEmptyException
     * @throws ValidateEntityUnsuccessfulException
     */
    public function handle(AMQPMessage $message): void
    {
        $message = json_decode($message->body, true);

        if (!isset($message['content']) || !isset($message['userId'])) {
            throw new MessageIsEmptyException('Message is empty!');
        }

        $user = $this->userRepository->getOneById((int) $message['userId']);

        $time = new \DateTime('now');
        $time->setTimezone(new \DateTimeZone('Europe/Warsaw'));

        $post = new Post();
        $post->setContent((string) $message['content']);
        $post->setCreatedAt($time);
        $post->setUpdatedAt($time);
        $post->setUserReferences($user);

        if (\count($this->validator->validate($post)) > 0) {
            throw new ValidateEntityUnsuccessfulException('Failed validate entity Post in: ' . \get_class($this));
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
