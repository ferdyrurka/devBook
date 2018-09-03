<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\SignInModel;
use App\Form\SignInForm;
use App\Form\SignUpForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return array
     *
     * @Route("/", methods={"GET"}, name="index.home")
     * @Template("home/index.html.twig")
     * @Security("not has_role('ROLE_USER')")
     */
    public function indexAction(Request $request): array
    {
        $signIn = $this->createForm(SignInForm::class, new SignInModel());
        $signIn->handleRequest($request);

        $signUp = $this->createForm(SignUpForm::class, new User());
        $signUp->handleRequest($request);

        return [
            'signIn' => $signIn->createView(),
            'signUp' => $signUp->createView(),
        ];
    }
}
