<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;
use App\Entity\User;
use App\Repository\MessageRepository;

/**
 * Class GetConversationListCommand
 * @package App\Command\API
 */
class GetConversationListCommand implements CommandInterface
{
    /**
     * @var User
     */
    private $user;

    /**
     * GetConversationListCommand constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
