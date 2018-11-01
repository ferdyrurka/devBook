<?php
declare(strict_types=1);

namespace App\Command\Web;

use App\Command\CommandInterface;
use App\Entity\User;
use App\Entity\UserToken;
use \DateTime;

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
        $time = new DateTime('now');

        $this->user->setCreatedAt($time);
        $this->user->setRoles('ROLE_USER');
        $this->user->setStatus(1);

        return $this->user;
    }

    /**
     * @return UserToken
     * @throws \Exception
     */
    public function getUserToken(): UserToken
    {
        $mobileTokenTime = new DateTime('+10 day');
        $webTokenTime = new DateTime('+1 day');
        $publicTokenTime = new DateTime('+30 day');

        $userToken = new UserToken();
        $userToken->setRefreshMobileToken($mobileTokenTime);
        $userToken->setRefreshWebToken($webTokenTime);
        $userToken->setRefreshPublicToken($publicTokenTime);

        return $userToken;
    }
}
