<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class AddMessageCommand
 * @package App\Command\Console\DevMessenger
 */
class AddMessageCommand implements CommandInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AddMessageCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @var Message
     */
    private $message;

    /**
     * @param Message $message
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Message
     */
    private function getMessage(): Message
    {
        return $this->message;
    }

    public function execute(): void
    {
        $message = $this->getMessage();
        $time = new \DateTime("now");
        $time->setTimezone(new \DateTimeZone("Europe/Warsaw"));
        $message->setSendTime($time);

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
