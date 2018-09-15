<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var User
     */
    private $user;

    /**
     * CreateUserCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        $timeZone = new \DateTimeZone('Europe/Warsaw');

        $time = new \DateTime("now");
        $time->setTimezone($timeZone);

        $mobileTokenTime = new \DateTime("+10 day");
        $mobileTokenTime->setTimezone($timeZone);

        $webTokenTime = new \DateTime("+1 day");
        $webTokenTime->setTimezone($timeZone);

        $publicTokenTime = new \DateTime("+30 day");
        $publicTokenTime->setTimezone($timeZone);

        $userToken = new UserToken();
        $userToken->setRefreshMobileToken($mobileTokenTime);
        $userToken->setRefreshWebToken($webTokenTime);
        $userToken->setRefreshPublicToken($publicTokenTime);

        $this->user->setCreatedAt($time);
        $this->user->setRoles('ROLE_USER');
        $this->user->setStatus(1);
        $this->user->setPassword($this->passwordEncoder->encodePassword($this->user, $this->user->getPlainPassword()));
        $this->user->setUserTokenReferences($userToken);

        $this->entityManager->persist($userToken);
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }
}
