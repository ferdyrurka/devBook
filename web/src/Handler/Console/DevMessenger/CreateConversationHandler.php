<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Conversation;
use App\Event\CreateConversationEvent;
use App\Exception\InvalidException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\HandlerInterface;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\RedisService;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateConversation
 * @package App\Command\Console\DevMessenger
 */
class CreateConversationHandler implements HandlerInterface
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    /**
     * @var Client
     */
    private $redis;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CreateConversationHandler constructor.
     * @param ConversationRepository $conversationRepository
     * @param UserRepository $userRepository
     * @param RedisService $redis
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ConversationRepository $conversationRepository,
        UserRepository $userRepository,
        RedisService $redis,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->redis = $redis;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CommandInterface $createConversationCommand
     * @throws InvalidException
     * @throws \Exception
     */
    public function handle(CommandInterface $createConversationCommand): void
    {
        /**
         * Validate arguments
         */

        if (($sendToken = $createConversationCommand->getSendUserToken()) ===
            ($receiveToken = $createConversationCommand->getReceiveUserToken())
        ) {
            throw new InvalidException('Token is equal. Token is must not equal. Token value: ' . $sendToken);
        }

        $receiveUser = $this->userRepository->getOneByPublicToken($receiveToken);
        $sendUser = $this->userRepository->getOneByPrivateWebTokenOrMobileToken($sendToken);

        if ($this->userRepository->getCountConversationByUsersId(
            $sendUser->getId(),
            $receiveUser->getId()
        ) > 0 ) {
            $this->sendEvent(['result' => false]);
            return;
        }

        /**
         * Save
         */

        #MySql

        $conversation = new Conversation();
        $conversation
            ->addConversation($sendUser)
            ->addConversation($receiveUser)
        ;

        if (\count($this->validator->validate($conversation)) > 0) {
            throw new ValidateEntityUnsuccessfulException(
                'Failed validation entity Conversation in: ' . \get_class($this)
            );
        }

        $this->conversationRepository->save($conversation);

        #Redis

        $usersToken = [];

        $sendUser = $sendUser->getUserTokenReferences();
        $usersToken[] = $sendUser->getPrivateMobileToken();
        $usersToken[] = $sendUser->getPrivateWebToken();

        $receiveUserToken = $receiveUser->getUserTokenReferences();
        $usersToken[] = $receiveUserToken->getPrivateWebToken();
        $usersToken[] = $receiveUserToken->getPrivateMobileToken();

        $this->redis->setDatabase(2)->set($conversation->getConversationId(), json_encode([
            $usersToken
        ]));

        $this->sendEvent([
            'fullName' => $receiveUser->getFirstName() . ' ' . $receiveUser->getSurname(),
            'conversationId' => $conversation->getConversationId(),
            'result' => true
        ]);
    }

    private function sendEvent(array $result): void
    {
        $event = new CreateConversationEvent($result);
        $this->eventDispatcher->dispatch(CreateConversationEvent::NAME, $event);
    }
}
