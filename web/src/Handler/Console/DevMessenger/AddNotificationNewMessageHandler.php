<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Composite\RabbitMQ\Send\AddNotification;
use App\Composite\RabbitMQ\SendComposite;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;

/**
 * Class AddNotificationNewMessageHandler
 * @package App\Handler\Console\DevMessenger
 */
class AddNotificationNewMessageHandler implements HandlerInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(CommandInterface $addNotificationNewMessageCommand): void
    {
        $user = $this->userRepository->getOneByPrivateWebTokenOrMobileToken($addNotificationNewMessageCommand->getUserFromToken());
        $userToken = $user->getUserTokenReferences();
        $userSendNotification = $addNotificationNewMessageCommand->getUserToken();

        if ($userToken->getPrivateWebToken() === $userSendNotification || $userToken->getPrivateMobileToken() === $userSendNotification) {
            return;
        }

        $sendComposite = new SendComposite();
        $sendComposite->add(new AddNotification(
            'You have a new message from: ' . $user->getFirstName() . ' ' . $user->getSurname(),
            $userSendNotification
        ));
        $sendComposite->run();
    }
}

