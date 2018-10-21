<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\GetConversationListCommand;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @IsGranted("ROLE_USER")
     */
    public function getConversationListAction(
        CommandService $commandService,
        GetConversationListCommand $command
    ): JsonResponse {
        if (empty($user = $this->getUser())) {
            throw new UserNotFoundException('User not found!');
        }

        $command->setUser($user);
        $commandService->setCommand($command);
        $commandService->execute();

        return new JsonResponse($commandService->getResult());
    }
}
