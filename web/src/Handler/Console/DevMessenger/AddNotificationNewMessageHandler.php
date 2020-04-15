<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Composite\RabbitMQ\Send\AddNotification;
use App\Composite\RabbitMQ\SendComposite;
use App\Event\AddNotificationNewMessageEvent;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AddNotificationNewMessageHandler constructor.
     * @param UserRepository $userRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(UserRepository $userRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CommandInterface $addNotificationNewMessageCommand
     */
    public function handle(CommandInterface $addNotificationNewMessageCommand): void
    {
        $user = $this->userRepository->getOneByPrivateTokens($addNotificationNewMessageCommand->getUserFromToken());
        $userToken = $user->getUserTokenReferences();
        $userSendNotification = $addNotificationNewMessageCommand->getUserToken();

        if ($userToken->getPrivateWebToken() === $userSendNotification || $userToken->getPrivateMobileToken() === $userSendNotification) {
            $this->sendEvent(false);
            return;
        }

        $sendComposite = new SendComposite();
        $sendComposite->add(new AddNotification(
            'You have a new message from: ' . $user->getFirstName() . ' ' . $user->getSurname(),
            $userSendNotification
        ));
        $sendComposite->run();

        $this->sendEvent(true);
    }

    /**
     * @param bool $result
     */
    private function sendEvent(bool $result): void
    {
        $event = new AddNotificationNewMessageEvent($result);
        $this->eventDispatcher->dispatch(AddNotificationNewMessageEvent::NAME, $event);
    }
}

