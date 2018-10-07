<?php
declare(strict_types=1);

namespace App\Tests\Command\Console\DevMessenger;

use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Entity\Conversation;
use App\Entity\User;
use App\Entity\UserToken;
use App\Exception\ConversationExistException;
use App\Exception\InvalidException;
use App\Repository\UserRepository;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;

/**
 * Class CreateConversationCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class CreateConversationCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('set')->once();

        $redisService = Mockery::mock(RedisService::class);
        $redisService->shouldReceive('setDatabase')->withArgs([2])->andReturn($client);

        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->once()->withArgs([Conversation::class]);
        $entityManager->shouldReceive('flush')->once();

        #User receive

        $userTokenReceive = Mockery::mock(UserToken::class);
        $userTokenReceive->shouldReceive('getPrivateWebToken')->once()->andReturn('receive_private_user_token');
        $userTokenReceive->shouldReceive('getPrivateMobileToken')->once()
            ->andReturn('receive_private_mobile_user_token');

        $userReceive = Mockery::mock(User::class);
        $userReceive->shouldReceive('getFirstName')->once()->andReturn('FirstName');
        $userReceive->shouldReceive('getSurname')->once()->andReturn('Surname');
        $userReceive->shouldReceive('getId')->andReturn(2);
        $userReceive->shouldReceive('getUserTokenReferences')->once()->andReturn($userTokenReceive);

        #Send

        $userTokenSend = Mockery::mock(UserToken::class);
        $userTokenSend->shouldReceive('getPrivateWebToken')->once()->andReturn('send_private_user_token');
        $userTokenSend->shouldReceive('getPrivateMobileToken')->once()
            ->andReturn('send_private_mobile_user_token');

        $userSend = Mockery::mock(User::class);
        $userSend->shouldReceive('getId')->andReturn(1);
        $userSend->shouldReceive('getUserTokenReferences')->once()->andReturn($userTokenSend);

        #User Repository

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPublicToken')->withArgs(['receive_user_token'])
            ->times(2)->andReturn($userReceive);
        $userRepository->shouldReceive('getOneByPrivateWebToken')->withArgs(['send_user_token'])
            ->times(2)->andReturn($userSend);
        $userRepository->shouldReceive('getCountConversationByUsersId')->times(2)->withArgs([1, 2])->andReturn(0, 1);

        $createConversationCommand = new CreateConversationCommand(
            $entityManager,
            $userRepository,
            $redisService
        );
        $createConversationCommand->setSendUserToken('send_user_token');
        $createConversationCommand->setReceiveUserToken('receive_user_token');

        $this->assertNull($createConversationCommand->execute());

        $result = $createConversationCommand->getResult();

        $this->assertNotNull($result['conversationId']);
        $this->assertTrue($result['result']);
        $this->assertEquals('FirstName Surname', $result['fullName']);

        $this->conversationExistExceptionTest($createConversationCommand);
    }

    /**
     * @param CreateConversationCommand $createConversationCommand
     * @throws \Exception
     */
    public function conversationExistExceptionTest(CreateConversationCommand $createConversationCommand): void
    {
        $this->expectException(ConversationExistException::class);
        $createConversationCommand->execute();
        $result = $createConversationCommand->getResult();
        $this->assertFalse($result['result']);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidException(): void
    {
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $userRepository = Mockery::mock(UserRepository::class);
        $redisService = Mockery::mock(RedisService::class);
        $client = Mockery::mock(Client::class);
        $redisService->shouldReceive('setDatabase')->withArgs([2])->andReturn($client);

        $createConversationCommand = new CreateConversationCommand(
            $entityManager,
            $userRepository,
            $redisService
        );

        $createConversationCommand->setSendUserToken('send_user_token');
        $createConversationCommand->setReceiveUserToken('send_user_token');

        $this->expectException(InvalidException::class);
        $createConversationCommand->execute();
        $result = $createConversationCommand->getResult();
        $this->assertFalse($result['result']);
    }
}
