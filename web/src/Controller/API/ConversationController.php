<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\GetConversationListCommand;
use App\Event\GetConversationListEvent;
use App\EventListener\GetConversationListEventListener;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConversationController
 * @package App\Controller\API
 */
class ConversationController extends Controller
{
    /**
     * @param CommandService $commandService
     * @param EventDispatcherInterface $eventDispatcher
     * @return JsonResponse
     * @throws \App\Exception\LackHandlerToCommandException
     * @Route("/api/get-conversation-list", methods={"GET"}, name="getConversationList.conversation")
     * @IsGranted("ROLE_USER")
     */
    public function getConversationListAction(
        CommandService $commandService,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {
        if (empty($user = $this->getUser())) {
            throw new UserNotFoundException('User not found!');
        }

        $listener = new GetConversationListEventListener();
        $eventDispatcher->addListener(GetConversationListEvent::NAME, [$listener, 'setConversations']);

        $command = new GetConversationListCommand($user);
        $commandService->handle($command);

        return new JsonResponse($listener->getConversations());
    }
}
