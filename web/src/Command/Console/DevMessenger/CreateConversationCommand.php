<?php
declare(strict_types=1);

namespace App\Command\Console\DevMessenger;

use App\Command\CommandInterface;
use App\Entity\Conversation;
use App\Exception\ConversationExistException;
use App\Exception\InvalidException;
use App\Exception\UserNotFoundException;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;

/**
 * Class CreateConversation
 * @package App\Command\Console\DevMessenger
 */
class CreateConversationCommand implements CommandInterface
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
     * @var string
     */
    private $sendUserToken;

    /**
     * @var string
     */
    private $receiveUserToken;

    /**
     * @var Client
     */
    private $redis;

    /**
     * @var array
     */
    private $result;

    /**
     * CreateConversationCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param RedisService $redis
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        RedisService $redis
    ) {
        $this->redis = $redis;

        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    private function getSendUserToken(): string
    {
        return $this->sendUserToken;
    }

    /**
     * @param string $sendUserToken
     */
    public function setSendUserToken(string $sendUserToken): void
    {
        $this->sendUserToken = $sendUserToken;
    }

    /**
     * @return string
     */
    private function getReceiveUserToken(): string
    {
        return $this->receiveUserToken;
    }

    /**
     * @param string $receiveUserToken
     */
    public function setReceiveUserToken(string $receiveUserToken): void
    {
        $this->receiveUserToken = $receiveUserToken;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        /**
         * Validate arguments
         */

        if (($sendToken = $this->getSendUserToken()) === ($receiveToken = $this->getReceiveUserToken())) {
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
