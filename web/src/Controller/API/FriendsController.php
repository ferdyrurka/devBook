<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\SearchFriendsCommand;
use App\Event\SearchFriendsEvent;
use App\EventListener\SearchFriendsEventListener;
use App\Exception\InvalidException;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @param EventDispatcherInterface $eventDispatcher
     * @return JsonResponse
     * @throws InvalidException
     * @throws \App\Exception\LackHandlerToCommandException
     * @Route("/api/search-friends", methods={"GET"}, name="searchFriends.friends")
     * @IsGranted("ROLE_USER")
     */
    public function searchFriendsAction(
        CommandService $commandService,
        Request $request,
        EventDispatcherInterface $eventDispatcher
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

        $searchFriendsEvent = new SearchFriendsEventListener();
        $eventDispatcher->addListener(SearchFriendsEvent::NAME, [$searchFriendsEvent, 'setUsers']);

        $searchFriendsCommand = new SearchFriendsCommand($user->getId(), $phrase);
        $commandService->handle($searchFriendsCommand);

        return new JsonResponse($searchFriendsEvent->getUsers());
    }
}
