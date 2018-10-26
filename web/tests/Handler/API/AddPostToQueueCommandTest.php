<?php
declare(strict_types=1);

namespace App\Tests\Command\API;

use App\Command\API\AddPostToQueueCommand;
use App\Composite\RabbitMQ\Send\AddPost;
use App\Composite\RabbitMQ\SendComposite;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class AddPostToQueueCommandTest
 * @package App\Tests\Command\API
 */
class AddPostToQueueCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute(): void
    {
        $sendComposite = Mockery::mock(SendComposite::class);
        $sendComposite->shouldReceive('add')->withArgs([AddPost::class])->once();
        $sendComposite->shouldReceive('run')->once();

        $addPostToQueue = new AddPostToQueueCommand($sendComposite);

        $addPostToQueue->setContent('content');
        $addPostToQueue->setUserId(1);
        $addPostToQueue->execute();
    }
}

