<?php
declare(strict_types=1);

namespace App\Event;

use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;
use \DateTimeZone;
use \DateTime;

/**
 * Class RefreshTokenEventListener
 */
class RefreshTokenEventListener implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @throws \Exception
     */
    public function onKernelController(): void
    {
        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        $userToken = $user->getUserTokenReferences();

        $date = new DateTime('now', new DateTimeZone('Europe/Warsaw'));
        $date = $date->getTimestamp();
        $save = false;

        if ($userToken->getRefreshMobileToken()->getTimestamp() <= $date) {
            $userToken->setRefreshMobileToken(new DateTime('+10 day'), new DateTimeZone('Europe/Warsaw'));
            $userToken->setPrivateMobileToken((string) Uuid::uuid4());

            $save = true;
        }

        if ($userToken->getRefreshWebToken()->getTimestamp() <= $date) {
            $userToken->setRefreshWebToken(new DateTime('+1 day'), new DateTimeZone('Europe/Warsaw'));
            $userToken->setPrivateWebToken((string) Uuid::uuid4());

            $save = true;
        }

        if ($userToken->getRefreshPublicToken()->getTimestamp() <= $date) {
            $userToken->setRefreshPublicToken(new DateTime('+30 day'), new DateTimeZone('Europe/Warsaw'));
            $userToken->setPublicToken((string) Uuid::uuid4());

            $save = true;
        }


        if ($save) {
            $this->saveUserToken($userToken);
        }
    }

    private function saveUserToken(UserToken $userToken) :void
    {
        $this->entityManager->persist($userToken);
        $this->entityManager->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
        );
    }
}

