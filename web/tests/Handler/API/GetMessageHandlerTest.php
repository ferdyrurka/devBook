<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\GetMessageCommand;
use App\Entity\Message;
use App\Exception\InvalidException;
use App\Handler\API\GetMessageHandler;
use App\Repository\MessageRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class GetMessageCommandTest
 * @package App\Tests\Command\API
 */
class GetMessageHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @throws InvalidException
     */
    public function testExecute(): void
    {
        $time = new \DateTime("now");

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

        $getMessageHandler = new GetMessageHandler($messageRepository);
        $getMessageHandler->handle($getMessageCommand);

        $result = $getMessageHandler->getResult();
        $this->assertEquals('Message receive', $result[0]['message']);
        $this->assertEquals('Message send', $result[1]['message']);

        $this->assertEquals('Receive', $result[0]['template']);
        $this->assertEquals('From', $result[1]['template']);

        $this->assertEquals($time->format('Y-m-d H:i:s'), $result[0]['date']);
        $this->assertEquals($time->format('Y-m-d H:i:s'), $result[1]['date']);
    }

    public function testInvalidArguments(): void
    {
        $messageRepository = Mockery::mock(MessageRepository::class);

        $getMessageHandler = new GetMessageHandler($messageRepository);

        $getMessageCommand = new GetMessageCommand(1, 'FAILED', 0);

        $this->expectException(InvalidException::class);
        $getMessageHandler->handle($getMessageCommand);
    }
}
