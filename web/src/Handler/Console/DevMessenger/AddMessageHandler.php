<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Exception\ConversationNotExistException;
use App\Exception\NotAuthorizationUUIDException;
use App\Exception\UserNotFoundException;
use App\Exception\UserNotFoundInConversationException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\HandlerInterface;
use App\Repository\ConversationRepository;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddMessageCommand
 * @package App\Command\Console\DevMessenger
 * Not set database in __constructor because WebSocket using pattern Singleton and not working variables.
 */
class AddMessageHandler implements HandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RedisService
     */
    private $redisService;

    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    /**
     * @var array
     */
    private $result;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * AddMessageCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param ConversationRepository $conversationRepository
     * @param RedisService $redisService
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationRepository,
        RedisService $redisService,
        ValidatorInterface $validator
    ) {
        $this->redisService = $redisService;
        $this->conversationRepository = $conversationRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @param string $conversationId
     * @return array
     * @throws ConversationNotExistException
     */
    private function addMissingConversation(string $conversationId): array
    {
        $conversation = $this->conversationRepository->getByConversationId($conversationId)[0];

        return $this->setConversationInRedis($conversation, $conversationId);
    }

    /**
     * @param Conversation $conversation
     * @param string $conversationId
     * @return array
     */
    private function setConversationInRedis(Conversation $conversation, string $conversationId): array
    {
        $conversationRedis = $this->redisService->setDatabase(2);

        $users = [];
        $valuesUser = $conversation->getUserReferences()->getValues();

        foreach ($valuesUser as $user) {
            $user = $user->getUserTokenReferences();

            $users[] = $user->getPrivateWebToken();
            $users[] = $user->getPrivateMobileToken();
        }

        $conversationRedis->set($conversationId, json_encode($users));

        return $users;
    }

    /**
     * @param array $conversation
     * @param string $fromUserUuid
     * @param string $conversationId
     * @param int $userId
     * @throws UserNotFoundInConversationException
     */
    private function checkExistUserInConversation(
        array $conversation,
        string $fromUserUuid,
        string $conversationId,
        int $userId
    ): void {
        if (!\in_array($fromUserUuid, $conversation)) {
            $conversationEntity = $this->conversationRepository->findConversationByConversationIdAndUserId(
                $conversationId,
                $userId
            );

            if (!$conversationEntity) {
                throw new UserNotFoundInConversationException(
                    'User by UUID: ' . $fromUserUuid . ' not exist in conversation'
                );
            }

            $this->setConversationInRedis($conversationEntity[0], $conversationId);
        }
    }

    /**
     * @param string $fromUserUuid
     * @return array|null
     */
    private function getUserByUuid(string $fromUserUuid): ?array
    {
        $userUuidRedis = $this->redisService->setDatabase(1);
        $user = $userUuidRedis->get($fromUserUuid);

        if (empty($user)) {
            throw new UserNotFoundException('User not found by UUID: '. $fromUserUuid);
        }

        return json_decode($user, true);
    }

    /**
     * @param array $conversation
     * @param string $fromUserUuid
     * @return array
     */
    private function getReceiveUserMessageOrSendAllert(array $conversation, string $fromUserUuid): array
    {
        $userUuidRedis = $this->redisService->setDatabase(1);

        $result = [];

        foreach ($conversation as $userToken) {
            if ($userToken === $fromUserUuid) {
                continue;
            }

            $user = $userUuidRedis->get($userToken);

            if (empty($user)) {
                //Alert

                continue;
            }

            $user = json_decode($user, true);

            $result[] = $user['connId'];
        }

        return $result;
    }

    /**
     * @param CommandInterface $addMessageCommand
     * @throws ConversationNotExistException
     * @throws NotAuthorizationUUIDException
     * @throws UserNotFoundInConversationException
     * @throws ValidateEntityUnsuccessfulException
     */
    public function handle(CommandInterface $addMessageCommand): void
    {
        $userByConn = $this->redisService->setDatabase(0);

        /**
         * User not login and send message. Possible stealing a token.
         */
        $userConnId = $addMessageCommand->getFromId();
        $userPrivateToken = $userByConn->get($userConnId);

        if (empty($userPrivateToken)) {
            throw new UserNotFoundException('User not found! Doesn\'t register user! Conn id is: ' .$userConnId);
        }

        $message = $addMessageCommand->getMessage();

        /**
         * Check if the user has logged in with the same token
         */
        if ($message['userId'] !== $userPrivateToken) {
            throw new NotAuthorizationUUIDException(
                'Not authorization, maybe hack this UUID.
                ConnId: ' . $addMessageCommand->getFromId() . 'and Message: ' . json_encode($message)
            );
        }

        #Conversation valid

        $conversationRedis = $this->redisService->setDatabase(2);
        $conversationId = htmlspecialchars($message['conversationId']);
        $conversation = $conversationRedis->get($conversationId);

        $user = $this->getUserByUuid($userPrivateToken);

        if (empty($conversation)) {
            $conversation = $this->addMissingConversation($conversationId);
        } else {
            $conversation = json_decode($conversation, true);

            //If first message return in key 0 array
            if (\is_array($conversation[0])) {
                $conversation = $conversation[0];
            }
        }

        $this->checkExistUserInConversation(
            $conversation,
            $userPrivateToken,
            $conversationId,
            $user['id']
        );

        $this->result = $this->getReceiveUserMessageOrSendAllert(
            $conversation,
            $userPrivateToken
        );

        #Save in database mysql

        $messageEntity = new Message();
        $messageEntity->setMessage(htmlspecialchars($message['message']));
        $messageEntity->setConversationId($conversationId);
        $messageEntity->setSendUserId((int) $user['id']);

        $time = new \DateTime('now');
        $time->setTimezone(new \DateTimeZone('Europe/Warsaw'));
        $messageEntity->setSendTime($time);

        if (\count($this->validator->validate($messageEntity)) > 0) {
            throw new ValidateEntityUnsuccessfulException('Failed validation entity Message in: ' . \get_class($this));
        }

        $this->entityManager->persist($messageEntity);
        $this->entityManager->flush();
    }

    /**
     * @return array
     * Result is array users connId.
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
