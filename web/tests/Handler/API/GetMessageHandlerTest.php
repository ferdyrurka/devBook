<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\GetMessageCommand;
use App\Entity\Message;
use App\Event\GetMessageEvent;
use App\Exception\InvalidException;
use App\Handler\API\GetMessageHandler;
use App\Repository\MessageRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class GetMessageCommandTest
 * @package App\Tests\Command\API
 */
class GetMessageHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var array
     */
    private $result;

    /**
     * @throws InvalidException
     */
    public function testExecute(): void
    {
        $time = new \DateTime('now');

        $messageReceive = Mockery::mock(Message::class);
        $messageReceive->shouldReceive('getSendUserId')->once()->andReturn(2);
        $messageReceive->shouldReceive('getMessage')->once()->andReturn('Message receive');
        $messageReceive->shouldReceive('getSendTime')->once()->andReturn($time);

        $messageSend = Mockery::mock(Message::class);
        $messageSend->shouldReceive('getSendUserId')->once()->andReturn(1);
        $messageSend->shouldReceive('getMessage')->once()->andReturn('Message send');
        $messageSend->shouldReceive('getSendTime')->once()->andReturn($time);

        $messageRepository = Mockery::mock(MessageRepository::class);
        $messageRepository->shouldReceive('findByConversationId')->once()->withArgs([
                '8fdc55bd-6db4-46dd-8616-8dc786fe3eb0', 450, 30
            ])
            ->andReturn([1 => $messageReceive, 2 => $messageSend]);

        $getMessageCommand = new GetMessageCommand(1, '8fdc55bd-6db4-46dd-8616-8dc786fe3eb0', 15);

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->withArgs(function (string $name, $event) {
            if ($event instanceof GetMessageEvent && $name === GetMessageEvent::NAME) {
                $this->result = $event->getMessages();

                return true;
            }

            return false;
        })->once();

        $getMessageHandler = new GetMessageHandler($messageRepository, $eventDispatcher);
        $getMessageHandler->handle($getMessageCommand);

        $this->assertEquals('Message receive', $this->result[0]['message']);
        $this->assertEquals('Message send', $this->result[1]['message']);

        $this->assertEquals('Receive', $this->result[0]['template']);
        $this->assertEquals('From', $this->result[1]['template']);

        $this->assertEquals($time->format('Y-m-d H:i:s'), $this->result[0]['date']);
        $this->assertEquals($time->format('Y-m-d H:i:s'), $this->result[1]['date']);
    }

    public function testInvalidArguments(): void
    {
        $messageRepository = Mockery::mock(MessageRepository::class);

        $getMessageHandler = new GetMessageHandler(
            $messageRepository,
            Mockery::mock(EventDispatcherInterface::class)
        );

        $getMessageCommand = new GetMessageCommand(1, 'FAILED', 0);

        $this->expectException(InvalidException::class);
        $getMessageHandler->handle($getMessageCommand);
    }
}
