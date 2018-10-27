<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;

/**
 * Class CreateConversation
 * @package App\Command\Console\DevMessenger
 */
class CreateConversationCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $sendUserToken;

    /**
     * @var string
     */
    private $receiveUserToken;

    public function __construct(string $sendUserToken, string $receiveUserToken)
    {
        $this->sendUserToken = $sendUserToken;
        $this->receiveUserToken = $receiveUserToken;
    }

    /**
     * @return string
     */
    public function getSendUserToken(): string
    {
        return $this->sendUserToken;
    }

    /**
     * @return string
     */
    public function getReceiveUserToken(): string
    {
        return $this->receiveUserToken;
    }
}
