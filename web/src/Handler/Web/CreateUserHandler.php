<?php
declare(strict_types=1);

namespace App\Handler\Web;

use App\Command\CommandInterface;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateUserCommand
 * @package App\Command
 */
class CreateUserHandler implements HandlerInterface
{

    /**
     * @var UserTokenRepository
     */
    private $userTokenRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CreateUserHandler constructor.
     * @param UserRepository $userRepository
     * @param UserTokenRepository $userTokenRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface $validator
     */
    public function __construct(
        UserRepository $userRepository,
        UserTokenRepository $userTokenRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->userTokenRepository = $userTokenRepository;
    }

    /**
     * @param CommandInterface $createUserCommand
     * @throws ValidateEntityUnsuccessfulException
     * @throws \Exception
     */
    public function handle(CommandInterface $createUserCommand): void
    {
        $user = $createUserCommand->getUser();
        $userToken = $createUserCommand->getUserToken();

        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->setUserTokenReferences($userToken);

        if (\count($this->validator->validate($userToken)) > 0 || \count($this->validator->validate($userToken)) > 0) {
            throw new ValidateEntityUnsuccessfulException('Failed validate entity in: ' . \get_class($this));
        }

        $this->userTokenRepository->save($userToken);
        $this->userRepository->save($user);
    }
}
