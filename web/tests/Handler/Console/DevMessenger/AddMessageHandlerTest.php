<?php
declare(strict_types=1);

namespace App\Tests\Handler\Console\DevMessenger;

use App\Command\Console\DevMessenger\AddMessageCommand;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Entity\UserToken;
use App\Exception\NotAuthorizationUUIDException;
use App\Exception\UserNotFoundException;
use App\Exception\UserNotFoundInConversationException;
use App\Repository\ConversationRepository;
use App\Service\RedisService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;

/**
 * Class AddMessageCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class AddMessageCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws NotAuthorizationUUIDException
     * @throws \App\Exception\ConversationNotExistException
     * @throws \App\Exception\UserNotFoundInConversationException
     */
    public function testSendMessageAndCreateNewConversation(): void
    {
        $predis = Mockery::mock(Client::class);
        $predis->shouldReceive('get')->times(9)
            ->with(Mockery::on(function ($key) {
                if ($key === 2 ||
                    $key === 'conversationIdValue' ||
                    $key === 'privateWebToken' ||
                    $key === 'privateMobileToken' ||
                    $key === 'hello_world_uuid'
                ) {
                    return true;
                }

                return false;
            }))
            ->andReturn(
                #conn redis
                'hello_world_uuid',
                #Conversation redis
                json_encode([
                    'privateWebToken',
                    'privateMobileToken',
                    'hello_world_uuid'
                ]),
                #user by uuid (send user)
                json_encode(['id' => 1]),
                #Users send
                #Send this user using WebSocket
                json_encode(['connId' => 3]),
                #send alert
                null,

                # Conversation not exist

                #conn redis
                'hello_world_uuid',
                #Conversation
                null,
                #user by uuid (send user)
                json_encode(['id' => 1]),
                #user send
                #Send this user using WebSocket
                json_encode(['connId' => 4])
            );
        $predis->shouldReceive('set')->withArgs(['conversationIdValue',
            json_encode(['privateWebToken', 'hello_world_uuid'])])->once();

        $redis = Mockery::mock(RedisService::class);
        $redis->shouldReceive('setDatabase')->times(9)
            ->with(Mockery::on(function (int $databaseId) {
                /**
                 * 0 by ConnId return UserUUID
                 * 1 by UUID return UserId (in mysql) and connId
                 * 2 by ConversationId return all users token in conversation
                 */
                if ($databaseId === 1 || $databaseId === 2 || $databaseId === 0) {
                    return true;
                }

                return false;
            }))
            ->andReturn($predis);

        #Using in save history conversation (Mysql)

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->times(2)->withArgs([Message::class]);
        $entityManager->shouldReceive('flush')->times(2);

        #Using in tests wherein not exist conversation in redis

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getPrivateWebToken')->once()->andReturn('privateWebToken');
        $userToken->shouldReceive('getPrivateMobileToken')->once()->andReturn('hello_world_uuid');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getUserTokenReferences')->once()->andReturn($userToken);

        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('getValues')->once()->andReturn([$user]);

        $conversation = Mockery::mock(Conversation::class);
        $conversation->shouldReceive('getUserReferences')->once()->andReturn($collection);

        $conversationRepository = Mockery::mock(ConversationRepository::class);
        $conversationRepository->shouldReceive('getByConversationId')->andReturn([$conversation]);

        $message = [
            'userId' => 'hello_world_uuid',
            'conversationId' => 'conversationIdValue',
            'message' => 'messageValue'
        ];

        $addMessage = new AddMessageCommand($entityManager, $conversationRepository, $redis);
        $addMessage->setMessage($message);
        $addMessage->setFromId(2);
        $this->assertNull($addMessage->execute());

        $result = $addMessage->getResult();
        //Because return a in query json_encode(['connId' => 3]),
        $this->assertEquals(3, $result[0]);

        $addMessage->execute();

        $result = $addMessage->getResult();
        //Because return a in query json_encode(['connId' => 4]),
        $this->assertEquals(4, $result[0]);
    }


    /**
     * @throws NotAuthorizationUUIDException
     * @throws \App\Exception\ConversationNotExistException
     * @throws \App\Exception\UserNotFoundInConversationException
     */
    public function testUserNotFoundException(): void
    {
        $predis = Mockery::mock(Client::class);
        $predis->shouldReceive('get')->times(1)
            ->withArgs([1])->andReturn(null);

        $redis = Mockery::mock(RedisService::class);
        $redis->shouldReceive('setDatabase')->once()
            ->withArgs([0])
            ->andReturn($predis);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $conversationRepository = Mockery::mock(ConversationRepository::class);

        $addMessage = new AddMessageCommand($entityManager, $conversationRepository, $redis);
        $addMessage->setFromId(1);
        $addMessage->setMessage([]);

        $this->expectException(UserNotFoundException::class);
        $addMessage->execute();
    }

    /**
     * @throws NotAuthorizationUUIDException
     * @throws \App\Exception\ConversationNotExistException
     * @throws \App\Exception\UserNotFoundInConversationException
     */
    public function testNotAuthorizationUUIDException(): void
    {
        $predis = Mockery::mock(Client::class);
        $predis->shouldReceive('get')->times(1)
            ->withArgs([1])->andReturn('privateToken');

        $redis = Mockery::mock(RedisService::class);
        $redis->shouldReceive('setDatabase')->once()
            ->withArgs([0])
            ->andReturn($predis);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $conversationRepository = Mockery::mock(ConversationRepository::class);

        $addMessage = new AddMessageCommand($entityManager, $conversationRepository, $redis);
        $addMessage->setFromId(1);
        $addMessage->setMessage(['userId' => 'failedToken']);

        $this->expectException(NotAuthorizationUUIDException::class);
        $addMessage->execute();
    }

    /**
     * @throws NotAuthorizationUUIDException
     * @throws \App\Exception\ConversationNotExistException
     * @throws \App\Exception\UserNotFoundInConversationException
     */
    public function testUserNotFoundInConversationException(): void
    {
        $predis = Mockery::mock(Client::class);
        $predis->shouldReceive('get')->times(3)
            ->with(Mockery::on(function ($key) {
                if ($key === 1 || $key === 'conversationIdValue' || $key === 'privateToken') {
                    return true;
                }

                return false;
            }))
            ->andReturn('privateToken', json_encode(['notUserInConversation']), json_encode(['id' => 5]));

        $redis = Mockery::mock(RedisService::class);
        $redis->shouldReceive('setDatabase')->times(3)
            ->with(Mockery::on(function (int $databaseId) {
                if ($databaseId === 2 || $databaseId === 1 || $databaseId === 0) {
                    return true;
                }

                return false;
            }))
            ->andReturn($predis);

        $entityManager = Mockery::mock(EntityManagerInterface::class);

        $conversationRepository = Mockery::mock(ConversationRepository::class);
        $conversationRepository->shouldReceive('findConversationByConversationIdAndUserId')->once()
            ->withArgs(['conversationIdValue', 5])->andReturn(null);

        #Execute

        $addMessage = new AddMessageCommand($entityManager, $conversationRepository, $redis);
        $addMessage->setFromId(1);
        $addMessage->setMessage([
            'conversationId' => 'conversationIdValue',
            'userId' => 'privateToken'
        ]);

        $this->expectException(UserNotFoundInConversationException::class);
        $addMessage->execute();
    }
}
