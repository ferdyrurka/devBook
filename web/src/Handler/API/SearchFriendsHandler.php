<?php
declare(strict_types=1);

namespace App\Handler\API;

use App\Command\CommandInterface;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;

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
     * @var array
     */
    private $result;

    /**
     * GetFriendsCommand constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
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

        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
