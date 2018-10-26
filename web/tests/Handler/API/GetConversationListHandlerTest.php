<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\GetConversationListCommand;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Handler\API\GetConversationListHandler;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class GetConversationListCommandTest
 * @package App\Tests\Controller\API
 */
class GetConversationListHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testExecute()
    {
        //Other user who are this conversation

        $receiveUser = Mockery::mock(User::class);
        $receiveUser->shouldReceive('getId')->once()->andReturn(2);
        $receiveUser->shouldReceive('getFirstName')->once()->andReturn('FirstName');
        $receiveUser->shouldReceive('getSurname')->once()->andReturn('Surname');

        $collectionUser = Mockery::mock(Collection::class);
        $collectionUser->shouldReceive('getValues')->once()->andReturn([$receiveUser]);

        $conversation = Mockery::mock(Conversation::class);
        $conversation->shouldReceive('getConversationId')->once()->andReturn('conversation_token');
        $conversation->shouldReceive('getUserReferences')->once()->andReturn($collectionUser);

        //Users conversations

        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('getValues')->once()->andReturn([$conversation]);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(1);
        $user->shouldReceive('getConversationReferences')->once()->andReturn($collection);

        //Find last messages

        $message = Mockery::mock(Message::class);
        $message->shouldReceive('getMessage')->once()->andReturn('Hello World');

        $messageRepository = Mockery::mock(MessageRepository::class);
        $messageRepository->shouldReceive('getLastMessageByConversationId')->once()->andReturn($message);

        //Tests

        $getConversationListCommand = new GetConversationListCommand($user);

        $getConversationListHandler = new GetConversationListHandler($messageRepository);
        $getConversationListHandler->handle($getConversationListCommand);

        $result = $getConversationListCommand->getResult();
        $this->assertNotEmpty($result);
        $this->assertEquals('FirstName Surname', $result[0]['fullName']);
        $this->assertEquals('Hello World', $result[0]['lastMessage']);
        $this->assertEquals('conversation_token', $result[0]['conversationId']);
    }
}
