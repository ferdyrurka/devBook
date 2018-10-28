<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Controller\API
 */
class NotificationController extends Controller
{
    /**
     * @return array
     * @Route("/notifications", methods={"GET"}, name="index.notification")
     * @Template("notification/index.html.twig")
     * @IsGranted("ROLE_USER")
     */
    public function indexAction(): array
    {
        if (($user = $this->getUser()) === null) {
            throw new UserNotFoundException('User not found! In method "indexAction" from: ' . \get_class($this));
        }

        return [
            'notifications' => $user->getNotificationReferences()->getValues()
        ];
    }
}

