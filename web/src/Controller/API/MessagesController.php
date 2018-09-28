<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\GetMessageCommand;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessagesController
 * @package App\Controller\API
 */
class MessagesController extends Controller
{
    /**
     * @param GetMessageCommand $getMessageCommand
     * @param CommandService $commandService
     * @param string $conversationId
     * @param int $offset
     * @throws /App/Exception/InvalidException
     * @return JsonResponse
     * @Route("/api/get-messages/{conversationId}/{offset}", methods={"GET"}, name="getMessages.message")
     * @IsGranted("ROLE_USER")
     */
    public function getMessagesAction(
        GetMessageCommand $getMessageCommand,
        CommandService $commandService,
        string $conversationId,
        int $offset = 0
    ): JsonResponse {
        $getMessageCommand->setConversationId($conversationId);
        $getMessageCommand->setUserId($this->getUser()->getId());
        $getMessageCommand->setOffset($offset);

        $commandService->setCommand($getMessageCommand);
        $commandService->execute();

        return new JsonResponse([
            $commandService->getResult(),
        ]);
    }
}
