<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Event\AddMessageEvent;
use App\Exception\ConversationNotExistException;
use App\Exception\NotAuthorizationUUIDException;
use App\Exception\UserNotFoundException;
use App\Exception\UserNotFoundInConversationException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\HandlerInterface;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\RedisService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddMessageCommand
 * @package App\Command\Console\DevMessenger
 * Not set database in __constructor because WebSocket using pattern Singleton and not working variables.
 */
class AddMessageHandler implements HandlerInterface
{
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var RedisService
     */
    private $redisService;

    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AddMessageHandler constructor.
     * @param MessageRepository $messageRepository
     * @param ConversationRepository $conversationRepository
     * @param RedisService $redisService
     * @param ValidatorInterface $validator
     */
    public function __construct(
        MessageRepository $messageRepository,
        ConversationRepository $conversationRepository,
        RedisService $redisService,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->redisService = $redisService;
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
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
     * @param string $userPrivateToken
     * @param string $conversationId
     * @param int $userId
     * @throws UserNotFoundInConversationException
     */
    private function checkExistUserInConversation(
        array $conversation,
        string $userPrivateToken,
        string $conversationId,
        int $userId
    ): void {
        if (!\in_array($userPrivateToken, $conversation)) {
            $conversationEntity = $this->conversationRepository->findConversationByConversationIdAndUserId(
                $conversationId,
                $userId
            );

            if (!$conversationEntity) {
                throw new UserNotFoundInConversationException(
                    'User by UUID: ' . $userPrivateToken . ' not exist in conversation'
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
        /**
         * Table user key is userId (online) value is connId and UserId in Mysql
         */
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
    private function getReceiveUserMessageOrSendNotification(array $conversation, string $fromUserUuid): array
    {
        /**
         * Table user key is userId (online) value is connId and UserId in Mysql
         */
        $userUuidRedis = $this->redisService->setDatabase(1);

        $result = [];
        $sendNotificationId = 0;

        foreach ($conversation as $privateUserToken) {
            if ($privateUserToken === $fromUserUuid) {
                continue;
            }

            $user = $userUuidRedis->get($privateUserToken);

            if (empty($user)) {
                $result['notification'][$sendNotificationId] = $privateUserToken;
                ++$sendNotificationId;

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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handle(CommandInterface $addMessageCommand): void
    {
        /**
         * User online Database
         */
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
        if (htmlspecialchars($message['userId']) !== $userPrivateToken) {
            throw new NotAuthorizationUUIDException(
                'Not authorization, maybe hack this UUID.
                ConnId: ' . $addMessageCommand->getFromId() . 'and Message: ' . json_encode($message)
            );
        }

        /**
         * Conversation valid
         * Database key is conversationId value all users in conversation
         */
        $conversationRedis = $this->redisService->setDatabase(2);
        $conversationId = htmlspecialchars($message['conversationId']);
        $conversation = $conversationRedis->get($conversationId);

        if (empty($conversation)) {
            $conversation = $this->addMissingConversation($conversationId);
        } else {
            $conversation = json_decode($conversation, true);

            //If first message return in key 0 array
            if (\is_array($conversation[0])) {
                $conversation = $conversation[0];
            }
        }

        $user = $this->getUserByUuid($userPrivateToken);

        /**
         * Check exist user and get receive user or send notification
         */

        $this->checkExistUserInConversation(
            $conversation,
            $userPrivateToken,
            $conversationId,
            (int) $user['id']
        );

        $result = $this->getReceiveUserMessageOrSendNotification(
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

        $this->messageRepository->save($messageEntity);

        $event = new AddMessageEvent($result);
        $this->eventDispatcher->dispatch(AddMessageEvent::NAME, $event);
    }
}
