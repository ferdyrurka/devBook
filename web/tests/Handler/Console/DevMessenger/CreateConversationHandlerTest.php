<?php
declare(strict_types=1);

namespace App\Tests\Handler\Console\DevMessenger;

use App\Command\Console\DevMessenger\CreateConversationCommand;
use App\Entity\Conversation;
use App\Entity\User;
use App\Entity\UserToken;
use App\Event\CreateConversationEvent;
use App\Exception\InvalidException;
use App\Exception\ValidateEntityUnsuccessfulException;
use App\Handler\Console\DevMessenger\CreateConversationHandler;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Predis\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CreateConversationCommandTest
 * @package App\Tests\Command\Console\DevMessenger
 */
class CreateConversationHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var array
     */
    private $result;

    /**
     * @throws \Exception
     */
    public function testExecute(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('set')->once();

        $redisService = Mockery::mock(RedisService::class);
        $redisService->shouldReceive('setDatabase')->withArgs([2])->andReturn($client);

        $conversationRepository= Mockery::mock(ConversationRepository::class);
        $conversationRepository->shouldReceive('save')->once()->withArgs([Conversation::class]);

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
        $userRepository->shouldReceive('getOneByPrivateWebTokenOrMobileToken')->withArgs(['send_user_token'])
            ->times(2)->andReturn($userSend);
        $userRepository->shouldReceive('getCountConversationByUsersId')->times(2)->withArgs([1, 2])->andReturn(0, 0);

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')->withArgs([Conversation::class])->andReturn([], ['failed'])->times(2);

        #Event

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->withArgs(function (string $name, $event) {
            if ($event instanceof CreateConversationEvent && $name === CreateConversationEvent::NAME) {
                $this->result = $event->getConversation();
                return true;
            }

            return false;
        })->once();

        #Handler and Command

        $createConversationHandler = new CreateConversationHandler(
            $conversationRepository,
            $userRepository,
            $redisService,
            $validator,
            $eventDispatcher
        );

        $createConversationCommand = new CreateConversationCommand('send_user_token', 'receive_user_token');

        $createConversationHandler->handle($createConversationCommand);

        $this->assertNotNull($this->result['conversationId']);
        $this->assertTrue($this->result['result']);
        $this->assertEquals('FirstName Surname', $this->result['fullName']);

        $this->expectException(ValidateEntityUnsuccessfulException::class);
        $createConversationHandler->handle($createConversationCommand);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidException(): void
    {
        $conversationRepository= Mockery::mock(ConversationRepository::class);
        $userRepository = Mockery::mock(UserRepository::class);
        $redisService = Mockery::mock(RedisService::class);
        $client = Mockery::mock(Client::class);
        $redisService->shouldReceive('setDatabase')->withArgs([2])->andReturn($client);

        $createConversationHandler = new CreateConversationHandler(
            $conversationRepository,
            $userRepository,
            $redisService,
            Mockery::mock(ValidatorInterface::class),
            Mockery::mock(EventDispatcherInterface::class)
        );

        $createConversationCommand = new CreateConversationCommand('send_user_token', 'send_user_token');

        $this->expectException(InvalidException::class);
        $createConversationHandler->handle($createConversationCommand);
    }
}
