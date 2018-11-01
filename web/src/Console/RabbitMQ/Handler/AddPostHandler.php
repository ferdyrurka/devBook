<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ\Handler;

use App\Exception\MessageIsEmptyException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
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
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * AddPostHandler constructor.
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(
        PostRepository $postRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ) {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    /**
     * @param AMQPMessage $message
     * @throws MessageIsEmptyException
     * @throws ValidateEntityUnsuccessfulException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handle(AMQPMessage $message): void
    {
        $message = json_decode($message->body, true);

        if (!isset($message['content'], $message['userId']) ||
            !\is_int($message['userId']) || !\is_string($message['content'])
        ) {
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

        $this->postRepository->save($post);
    }
}
