<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DevMessageController
 * @package App\Controller
 */
class DevMessengerController extends Controller
{
    /**
     * @return array
     * @Route("/dev-messenger", methods={"GET"}, name="index.devMessage")
     * @Template("devMessage/index.html.twig")
     * @IsGranted("ROLE_USER")
     */
    public function indexAction(): array
    {
        if (empty($user = $this->getUser())) {
            throw new UserNotFoundException('User not found!');
        }

        return [
            'userId' => $user->getUserTokenReferences()->getPrivateWebToken(),
        ];
    }
}
