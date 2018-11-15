<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\ValidateEntityUnsuccessfulException;
use App\Repository\UserTokenRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use \DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RefreshTokenEventListener
 */
class RefreshTokenEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var UserTokenRepository
     */
    private $userTokenRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        Security $security,
        UserTokenRepository $userTokenRepository,
        ValidatorInterface $validator
    ) {
        $this->userTokenRepository = $userTokenRepository;
        $this->security = $security;
        $this->validator = $validator;
    }

    /**
     * @throws ValidateEntityUnsuccessfulException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function onKernelController(): void
    {
        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        $userToken = $user->getUserTokenReferences();

        $date = new DateTime('now');
        $date = $date->getTimestamp();
        $save = false;

        if ($userToken->getRefreshMobileToken()->getTimestamp() <= $date) {
            $userToken->setRefreshMobileToken(new DateTime('+10 day'));
            $userToken->setPrivateMobileToken((string) Uuid::uuid4());

            $save = true;
        }

        if ($userToken->getRefreshWebToken()->getTimestamp() <= $date) {
            $userToken->setRefreshWebToken(new DateTime('+1 day'));
            $userToken->setPrivateWebToken((string) Uuid::uuid4());

            $save = true;
        }

        if ($userToken->getRefreshPublicToken()->getTimestamp() <= $date) {
            $userToken->setRefreshPublicToken(new DateTime('+30 day'));
            $userToken->setPublicToken((string) Uuid::uuid4());

            $save = true;
        }


        if ($save) {
            if (\count($this->validator->validate($userToken)) > 0) {
                throw new ValidateEntityUnsuccessfulException('Entity UserToken is failed in: ' . \get_class($this));
            }

            $this->userTokenRepository->save($userToken);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}

