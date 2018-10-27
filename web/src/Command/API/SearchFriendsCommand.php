<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;

/**
 * Class GetFriendsCommand
 * @package App\Command\API
 */
class SearchFriendsCommand implements CommandInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $phrase;

    /**
     * SearchFriendsCommand constructor.
     * @param int $userId
     * @param string $phrase
     */
    public function __construct(int $userId, string $phrase)
    {
        $this->userId = $userId;
        $this->phrase = $phrase;
    }

    /**
     * @return string
     */
    public function getPhrase(): string
    {
        return $this->phrase;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
