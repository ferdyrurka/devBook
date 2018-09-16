<?php
declare(strict_types=1);


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Message
 * @package App\Entity
 * @ORM\Table(name="message")
 * @ORM\Entity
 */
class Message
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, name="conversation_id")
     */
    private $conversationId;

    /**
     * @var string
     * @ORM\Column(type="string", length=10000)
     */
    private $message;

    /**
     * @var int
     * @ORM\Column(type="integer", length=11)
     */
    private $sendUserId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $sendTime;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    /**
     * @param string $conversationId
     */
    public function setConversationId(string $conversationId): void
    {
        $this->conversationId = $conversationId;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getSendUserId(): int
    {
        return $this->sendUserId;
    }

    /**
     * @param int $sendUserId
     */
    public function setSendUserId(int $sendUserId): void
    {
        $this->sendUserId = $sendUserId;
    }

    /**
     * @return \DateTime
     */
    public function getSendTime(): \DateTime
    {
        return $this->sendTime;
    }

    /**
     * @param \DateTime $sendTime
     */
    public function setSendTime(\DateTime $sendTime): void
    {
        $this->sendTime = $sendTime;
    }
}
