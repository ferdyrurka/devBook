<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;

/**
 * Class AddNotificationNewMessageCommand
 * @package App\Command\Console\DevMessenger
 */
class AddNotificationNewMessageCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $userFromToken;

    /**
     * @var string
     */
    private $userToken;

    public function __construct(string $userToken, string $userFromToken)
    {
        $this->userToken = $userToken;
        $this->userFromToken = $userFromToken;
    }

    /**
     * @return string
     */
    public function getUserToken(): string
    {
        return $this->userToken;
    }

    /**
     * @return string
     */
    public function getUserFromToken(): string
    {
        return $this->userFromToken;
    }
}
