<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\AddPostToQueueCommand;
use App\Composite\RabbitMQ\Send\AddPost;
use App\Composite\RabbitMQ\SendComposite;
use App\Handler\API\AddPostToQueueHandler;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class AddPostToQueueCommandTest
 * @package App\Tests\Command\API
 */
class AddPostToQueueHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $sendComposite = Mockery::mock(SendComposite::class);
        $sendComposite->shouldReceive('add')->withArgs([AddPost::class])->once();
        $sendComposite->shouldReceive('run')->once();

        $addPostToQueueCommand = new AddPostToQueueCommand('content', 1);

        $addPostToQueueHandler = new AddPostToQueueHandler($sendComposite);
        $addPostToQueueHandler->handle($addPostToQueueCommand);
    }
}
