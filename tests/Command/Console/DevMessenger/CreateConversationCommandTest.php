<?php
declare(strict_types=1);

namespace App\Tests\Command\Console\DevMessenger;

use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Entity\Conversation;
use App\Entity\User;
use App\Entity\UserToken;
use App\Exception\ConversationExistException;
use App\Exception\InvalidException;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class CreateConversationCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class CreateConversationCommandTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testExecute()
    {
        $entityManager = Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->once()->withArgs([Conversation::class]);
        $entityManager->shouldReceive('flush')->once();

        $conversationRepository = Mockery::mock(ConversationRepository::class);
        $conversationRepository->shouldReceive('getCountByUsersId')->times(2)->withArgs([1, 2])->andReturn(0, 1);

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getPrivateWebToken')->once()->andReturn('receive_private_user_token');

        $userReceive = Mockery::mock(User::class);
        $userReceive->shouldReceive('getFirstName')->once()->andReturn('FirstName');
        $userReceive->shouldReceive('getSurname')->once()->andReturn('Surname');
        $userReceive->shouldReceive('getId')->times(2)->andReturn(2);
        $userReceive->shouldReceive('getUserTokenReferences')->once()->andReturn($userToken);

        $userSend = Mockery::mock(User::class);
        $userSend->shouldReceive('getId')->times(2)->andReturn(1);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('getOneByPublicToken')->withArgs(['receive_user_token'])
            ->times(2)->andReturn($userReceive);
        $userRepository->shouldReceive('getOneByPrivateWebToken')->withArgs(['send_user_token'])
            ->times(2)->andReturn($userSend);

        $createConversationCommand = new CreateConversationCommand(
            $entityManager,
            $conversationRepository,
            $userRepository
        );
        $createConversationCommand->setSendUserToken('send_user_token');
        $createConversationCommand->setReceiveUserToken('receive_user_token');

        $this->assertNull($createConversationCommand->execute());

        $result = $createConversationCommand->getResult();

        $this->assertNotNull($result['conversationId']);
        $this->assertTrue($result['result']);
        $this->assertEquals('FirstName Surname', $result['fullName']);
        $this->assertEquals('send_user_token', $result['usersId'][0]);
        $this->assertEquals('receive_private_user_token', $result['usersId'][1]);

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
        $conversationRepository = Mockery::mock(ConversationRepository::class);
        $userRepository = Mockery::mock(UserRepository::class);

        $createConversationCommand = new CreateConversationCommand(
            $entityManager,
            $conversationRepository,
            $userRepository
        );

        $createConversationCommand->setSendUserToken('send_user_token');
        $createConversationCommand->setReceiveUserToken('send_user_token');

        $this->expectException(InvalidException::class);
        $createConversationCommand->execute();
        $result = $createConversationCommand->getResult();
        $this->assertFalse($result['result']);
    }
}
