<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\SearchFriendsCommand;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FriendsController
 * @package App\Controller\API
 */
class FriendsController extends Controller
{
    /**
     * @param CommandService $commandService
     * @param SearchFriendsCommand $searchFriendsCommand
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/search-friends", methods={"GET"}, name="searchFriends.friends")
     * @IsGranted("ROLE_USER")
     */
    public function searchFriendsAction(
        CommandService $commandService,
        SearchFriendsCommand $searchFriendsCommand,
        Request $request
    ): JsonResponse {
        $searchFriendsCommand->setPhrase($request->get('q'));
        $searchFriendsCommand->setUserId($this->getUser()->getId());

        $commandService->setCommand($searchFriendsCommand);
        $commandService->execute();

        return new JsonResponse($commandService->getResult());
    }
}
