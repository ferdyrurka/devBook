<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\SearchFriendsCommand;
use App\Exception\InvalidException;
use App\Exception\UserNotFoundException;
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
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidException
     * @Route("/api/search-friends", methods={"GET"}, name="searchFriends.friends")
     * @IsGranted("ROLE_USER")
     */
    public function searchFriendsAction(
        CommandService $commandService,
        Request $request
    ): JsonResponse {
        $phrase = $request->get('q');

        if (empty($phrase)) {
            throw new InvalidException(
                'Undefined variable q in url: /api/search-friends.
                User IP: ' . $request->getClientIp()
            );
        }

        if (empty($user = $this->getUser())) {
            throw new UserNotFoundException('User not found!');
        }

        $searchFriendsCommand = new SearchFriendsCommand($user->getId(), $phrase);
        $commandService->handle($searchFriendsCommand);

        return new JsonResponse($commandService->getResult());
    }
}
