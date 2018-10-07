<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\SearchFriendsCommand;
use App\Exception\InvalidException;
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
     * @throws InvalidException
     * @Route("/api/search-friends", methods={"GET"}, name="searchFriends.friends")
     * @IsGranted("ROLE_USER")
     */
    public function searchFriendsAction(
        CommandService $commandService,
        SearchFriendsCommand $searchFriendsCommand,
        Request $request
    ): JsonResponse {
        $phrase = $request->get('q');

        if (empty($phrase)) {
            throw new InvalidException(
                'Undefined variable q in url: /api/search-friends.
                User IP: ' . $request->getClientIp()
            );
        }

        $searchFriendsCommand->setPhrase($phrase);
        $searchFriendsCommand->setUserId($this->getUser()->getId());

        $commandService->setCommand($searchFriendsCommand);
        $commandService->execute();

        return new JsonResponse($commandService->getResult());
    }
}
