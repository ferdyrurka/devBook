<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\AddPostToQueueCommand;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use App\Service\PostService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @param Request $request
     * @param CommandService $service
     * @param AddPostToQueueCommand $addPostToQueueCommand
     * @return JsonResponse
     * @Route("/add-post", methods={"POST"}, name="addPost.post")
     * @IsGranted("ROLE_USER")
     */
    public function addPost(
        Request $request,
        CommandService $service
    ): JsonResponse {
        if (empty($user = $this->getUser())) {
            throw new UserNotFoundException('User not found!');
        }

        if (!empty($content = $request->get('content'))) {
            $addPostToQueueCommand = new AddPostToQueueCommand($content, $user->getId());

            $service->setCommand($addPostToQueueCommand);
            $service->execute();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['content' => false]);
    }
}
