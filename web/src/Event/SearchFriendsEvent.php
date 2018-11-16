<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SearchFriendsEvent
 * @package App\Event
 */
class SearchFriendsEvent extends Event
{
    /**
     * @var string
     */
    public const NAME = 'search.friends';

    /**
     * @var array
     */
    private $users;

    /**
     * SearchFriendsEvent constructor.
     * @param array $users
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return $this->users;
    }
}
