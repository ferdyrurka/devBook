<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Service\PostService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostController
 */
class PostController extends Controller
{
    /**
     * @param PostService $service
     * @return JsonResponse
     * @Route("/api/posts", methods={"GET"}, name="postsList.apiPost")
     * @IsGranted("ROLE_USER")
     */
    public function postsListAction(PostService $service): JsonResponse
    {
        return new JsonResponse($service->getPostsList());
    }
}
