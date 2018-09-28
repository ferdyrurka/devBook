<?php
declare(strict_types=1);


namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MainPageController
 * @package App\Controller
 */
class MainPageController extends Controller
{
    /**
     * @Route("/home", methods={"GET"}, name="home.mainPage")
     * @Template("mainPage/index.html.twig")
     * @IsGranted("ROLE_USER")
     */
    public function indexAction(): void
    {
        //Do nothing
    }
}
