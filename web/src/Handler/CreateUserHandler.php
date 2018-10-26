<?php
declare(strict_types=1);

namespace App\Handler;

use App\Command\CommandInterface;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class CreateUserCommand
 * @package App\Command
 */
class CreateUserHandler implements HandlerInterface
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
     * @param CommandInterface $createUserCommand
     * @throws \Exception
     */
    public function handle(CommandInterface $createUserCommand): void
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

        $user = $createUserCommand->getUser();

        $user->setCreatedAt($time);
        $user->setRoles('ROLE_USER');
        $user->setStatus(1);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setUserTokenReferences($userToken);

        $this->entityManager->persist($userToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
