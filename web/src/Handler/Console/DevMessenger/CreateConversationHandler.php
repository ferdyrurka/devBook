<?php
declare(strict_types=1);

namespace App\Handler\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Conversation;
use App\Exception\InvalidException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\HandlerInterface;
use App\Repository\UserRepository;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $redis;

    /**
     * @var array
     */
    private $result;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * CreateConversationCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param RedisService $redis
     * @param $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        RedisService $redis,
        ValidatorInterface $validator
    ) {
        $this->redis = $redis;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
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
        $sendUser = $this->userRepository->getOneByPrivateWebToken($sendToken);

        if ($this->userRepository->getCountConversationByUsersId(
            $sendUser->getId(),
            $receiveUser->getId()
        ) > 0 ) {
            $this->result['result'] = false;

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

        $this->entityManager->persist($conversation);
        $this->entityManager->flush();

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

        $this->result = [
            'fullName' => $receiveUser->getFirstName() . ' ' . $receiveUser->getSurname(),
            'conversationId' => $conversation->getConversationId(),
            'result' => true
        ];
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
