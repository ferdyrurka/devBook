<?php
declare(strict_types=1);

namespace App\Controller;

use App\Command\CreateUserCommand;
use App\Entity\User;
use App\Form\SignUpForm;
use App\Service\CommandService;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends Controller
{
    /**
     * @param Request $request
     * @param CreateUserCommand $command
     * @return Response
     * @Route("/register", methods={"POST"})
     * @Security("not has_role('ROLE_USER')")
     */
    public function signUpAction(Request $request, CreateUserCommand $command): Response
    {
        $form = $this->createForm(SignUpForm::class, new User());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command->setUser($form->getData());

            $commandService = new CommandService();
            $commandService->setCommand($command)->execute();
        }

        return $this->forward(HomeController::class . '::indexAction', [
            'request' => $request,
        ]);
    }
}
