<?php
declare(strict_types=1);

namespace App\Command\API;

use App\Command\CommandInterface;

/**
 * Class AddPostToQueueCommand
 * @package App\Command\API
 */
class AddPostToQueueCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $userId;

    /**
     * AddPostToQueueCommand constructor.
     * @param $content string
     * @param $userId integer
     */
    public function __construct(string $content, int $userId)
    {
        $this->content = $content;
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
