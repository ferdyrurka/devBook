<?php
declare(strict_types=1);

namespace App\Controller;

use App\Command\CreateUserCommand;
use App\Entity\User;
use App\Form\SignUpForm;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @throws \Exception
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

            return $this->redirectToRoute('index.home');
        }

        return $this->forward(HomeController::class . '::indexAction', [
            'request' => $request
        ]);
    }

    /**
     * @Route("/sign-in", methods={"POST"})
     * @Security("not has_role('ROLE_USER')")
     * @codeCoverageIgnore
     */
    public function signInAction(): void
    {
        //Do nothing
    }

    /**
     * @Route("/log-out", methods={"GET"}, name="logout.security")
     * @IsGranted("ROLE_USER")
     * @codeCoverageIgnore
     */
    public function logoutAction(): void
    {
       //Do nothing
    }
}
