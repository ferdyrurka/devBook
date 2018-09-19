<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\GetConversationListCommand;
use App\Service\CommandService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @param GetConversationListCommand $command
     * @return JsonResponse
     * @Route("/api/get-conversation-list", methods={"GET"}, name="getConversationList.conversation")
     */
    public function getConversationListAction(
        CommandService $commandService,
        GetConversationListCommand $command
    ): JsonResponse {
        $command->setUser($this->getUser());
        $commandService->setCommand($command);
        $commandService->execute();

        return new JsonResponse($commandService->getResult());
    }
}
