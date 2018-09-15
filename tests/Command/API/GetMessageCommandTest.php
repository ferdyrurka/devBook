<?php
declare(strict_types=1);

namespace App\Tests\Command\API;

use App\Command\API\GetMessageCommand;
use App\Entity\Message;
use App\Repository\MessageRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class GetMessageCommandTest
 * @package App\Tests\Command\API
 */
class GetMessageCommandTest extends TestCase
{
    public function testExecute()
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
        $messageRepository->shouldReceive('findByMessageId')->once()->withArgs(['message_id', 15, 30])
            ->andReturn([1 => $messageReceive, 2 => $messageSend]);

        $getMessageCommand = new GetMessageCommand($messageRepository);
        $getMessageCommand->setConversationId('message_id');
        $getMessageCommand->setUserId(1);
        $getMessageCommand->setOffset(15);
        $getMessageCommand->execute();

        $result = $getMessageCommand->getResult();
        $this->assertEquals('Message receive', $result[0]['message']);
        $this->assertEquals('Message send', $result[1]['message']);

        $this->assertEquals('Receive', $result[0]['template']);
        $this->assertEquals('From', $result[1]['template']);

        $this->assertEquals($time->format('Y-m-d H:i:s'), $result[0]['date']);
        $this->assertEquals($time->format('Y-m-d H:i:s'), $result[1]['date']);
    }
}
