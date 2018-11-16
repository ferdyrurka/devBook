<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Event\SearchFriendsEvent;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class GetFriendsCommand
 * @package App\Command\API
 */
class SearchFriendsHandler implements HandlerInterface
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
     * SearchFriendsHandler constructor.
     * @param UserRepository $userRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(UserRepository $userRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CommandInterface $searchFriendsCommand
     */
    public function handle(CommandInterface $searchFriendsCommand): void
    {
        $phrase = htmlspecialchars($searchFriendsCommand->getPhrase());
        $users = $this->userRepository->findByFirstNameOrSurname($phrase, $searchFriendsCommand->getUserId());

        $result = [];
        $i = 0;

        foreach ($users as $user) {
            $result[$i]['fullName'] = $user->getFirstName() . ' ' . $user->getSurname();
            $result[$i]['userId'] = $user->getUserTokenReferences()->getPublicToken();

            ++$i;
        }

        $searchFriendsEvent = new SearchFriendsEvent($result);
        $this->eventDispatcher->dispatch(SearchFriendsEvent::NAME, $searchFriendsEvent);
    }
}
