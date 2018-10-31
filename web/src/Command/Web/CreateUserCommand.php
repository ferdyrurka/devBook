<?php
declare(strict_types=1);

namespace App\Command\Web;

use App\Command\CommandInterface;
use App\Entity\User;

/**
 * Class CreateUserCommand
 * @package App\Command
 */
class CreateUserCommand implements CommandInterface
{
    /**
     * @var User
     */
    private $user;

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
