<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ\Handler;

use App\Entity\Notification;
use App\Exception\NotFullMessageException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddNotificationHandler extends RabbitMQHandlerAbstract
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @param AMQPMessage $jsonMessage
     * @throws NotFullMessageException
     * @throws ValidateEntityUnsuccessfulException
     */
    public function handle(AMQPMessage $jsonMessage): void
    {
        $message = json_decode($jsonMessage->body, true);

        if (!isset($message['userToken'], $message['notificationMessage']) ||
            !\is_string($message['userToken']) || !\is_string($message['notificationMessage'])
        ) {
            throw new NotFullMessageException('Not full message or failed type 
            in AddNotification handler. Message value: ' . \json_encode($message)
            );
        }

        $user = $this->userRepository->getOneByPrivateWebToken($message['userToken']);

        $notification = new Notification();
        $notification->setMessage($message['notificationMessage']);
        $notification->setDate(new \DateTime('now', new \DateTimeZone('Europe/Warsaw')));
        $notification->addUser($user);

        if (\count($this->validator->validate($notification)) > 0) {
            throw new ValidateEntityUnsuccessfulException('Validate entity is failed in: ' . \get_class($this));
        }

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}

