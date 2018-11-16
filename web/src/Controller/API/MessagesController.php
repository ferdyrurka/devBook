<?php
declare(strict_types=1);

namespace App\Controller\API;

use App\Command\API\GetMessageCommand;
use App\Event\GetMessageEvent;
use App\EventListener\GetMessageEventListener;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessagesController
 * @package App\Controller\API
 */
class MessagesController extends Controller
{
    /**
     * @param CommandService $commandService
     * @param string $conversationId
     * @param int $offset
     * @param EventDispatcherInterface $eventDispatcher
     * @throws /App/Exception/InvalidException
     * @return JsonResponse
     * @Route("/api/get-messages/{conversationId}/{offset}", methods={"GET"}, name="getMessages.message")
     * @IsGranted("ROLE_USER")
     */
    public function getMessagesAction(
        CommandService $commandService,
        EventDispatcherInterface $eventDispatcher,
        string $conversationId,
        int $offset = 0
    ): JsonResponse {
        if (empty($user = $this->getUser())) {
            throw new UserNotFoundException('User not found!');
        }

        $listener = new GetMessageEventListener();
        $eventDispatcher->addListener(GetMessageEvent::NAME, [$listener, 'setMessages']);

        $getMessageCommand = new GetMessageCommand($user->getId(), $conversationId, $offset);
        $commandService->handle($getMessageCommand);

        return new JsonResponse([
            $listener->getMessages()
        ]);
    }
}
