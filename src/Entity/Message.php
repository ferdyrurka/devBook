<?php
declare(strict_types=1);


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Class Message
 * @package App\Entity
 * @ORM\Table(name="message")
 * @ORM\Entity
 */
class Message
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $messageId;

    /**
     * @var string
     * @ORM\Column(type="string", length=10000)
     */
    private $messages;

    /**
     * Message constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->messageId = Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getMessages(): string
    {
        return $this->messages;
    }

    /**
     * @param string $messages
     */
    public function setMessages(string $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }
}
