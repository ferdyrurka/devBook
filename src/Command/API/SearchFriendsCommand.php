<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;
use App\Repository\UserRepository;

/**
 * Class GetFriendsCommand
 * @package App\Command\API
 */
class SearchFriendsCommand implements CommandInterface
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $phrase;

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
     * @return string
     */
    private function getPhrase(): string
    {
        return $this->phrase;
    }

    /**
     * @param string $phrase
     */
    public function setPhrase(string $phrase): void
    {
        $this->phrase = $phrase;
    }

    public function execute(): void
    {
        $phrase = htmlspecialchars($this->getPhrase());
        $users = $this->userRepository->findByFirstNameOrSurname($phrase);

        $result = [];
        $i = 0;

        foreach ($users as $user) {
            $result[$i]['fullName'] = $user->getFirstName() . ' ' . $user->getSurname();
            $result[$i]['userId'] = $user->getUserTokenReferences()->getPublicToken();
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
