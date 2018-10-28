<?php
declare(strict_types=1);

namespace App\Handler;

use App\Command\CommandInterface;
use App\Entity\UserToken;
use App\Exception\ValidateEntityUnsuccessfulException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CreateUserCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param CommandInterface $createUserCommand
     * @throws ValidateEntityUnsuccessfulException
     */
    public function handle(CommandInterface $createUserCommand): void
    {
        $timeZone = new \DateTimeZone('Europe/Warsaw');

        $time = new \DateTime('now');
        $time->setTimezone($timeZone);

        $mobileTokenTime = new \DateTime('+10 day');
        $mobileTokenTime->setTimezone($timeZone);

        $webTokenTime = new \DateTime('+1 day');
        $webTokenTime->setTimezone($timeZone);

        $publicTokenTime = new \DateTime('+30 day');
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

        if (\count($this->validator->validate($userToken)) > 0 || \count($this->validator->validate($userToken)) > 0) {
            throw new ValidateEntityUnsuccessfulException('Failed validate entity in: ' . \get_class($this));
        }

        $this->entityManager->persist($userToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
