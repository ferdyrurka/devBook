<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CreateUserCommand
 * @package App\Command
 */
class CreateUserCommand implements CommandInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var User
     */
    private $user;

    /**
     * CreateUserCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function execute(): void
    {
        $time = new \DateTime("now");
        $time->setTimezone(new \DateTimeZone('Europe/Warsaw'));

        $this->user->setCreatedAt($time);
        $this->user->setRoles('ROLE_USER');
        $this->user->setStatus(1);

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }
}
