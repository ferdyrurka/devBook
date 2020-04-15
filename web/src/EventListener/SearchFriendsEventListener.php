<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Event\SearchFriendsEvent;

/**
 * Class SearchFriendsEventListener
 * @package App\EventListener
 */
class SearchFriendsEventListener
{
    /**
     * @var array
     */
    private $users;

    /**
     * @param SearchFriendsEvent $searchFriendsEvent
     */
    public function setUsers(SearchFriendsEvent $searchFriendsEvent): void
    {
        $this->users = $searchFriendsEvent->getUsers();
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return $this->users;
    }
}
