<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\GetConversationListCommand;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Event\GetConversationListEvent;
use App\Handler\API\GetConversationListHandler;
use App\Repository\MessageRepository;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class GetConversationListCommandTest
 * @package App\Tests\Controller\API
 */
class GetConversationListHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var array
     */
    private $result;

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testExecute(): void
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

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->withArgs(function (string $name, $event) {
            if ($event instanceof GetConversationListEvent && $name === GetConversationListEvent::NAME) {
                $this->result = $event->getConversations();
                return true;
            }

            return false;
        })->once();

        //Tests

        $getConversationListCommand = new GetConversationListCommand($user);

        $getConversationListHandler = new GetConversationListHandler($messageRepository, $eventDispatcher);
        $getConversationListHandler->handle($getConversationListCommand);

        $this->assertNotEmpty($this->result);
        $this->assertEquals('FirstName Surname', $this->result[0]['fullName']);
        $this->assertEquals('Hello World', $this->result[0]['lastMessage']);
        $this->assertEquals('conversation_token', $this->result[0]['conversationId']);
    }
}
