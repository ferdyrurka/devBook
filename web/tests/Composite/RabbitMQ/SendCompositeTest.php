<?php
declare(strict_types=1);

namespace App\Tests\Composite\RabbitMQ;

use App\Composite\RabbitMQ\RabbitMQComponentAbstract;
use App\Composite\RabbitMQ\RabbitMQCompositeAbstract;
use App\Composite\RabbitMQ\SendComposite;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class SendCompositeTest
 * @package App\Tests\Composite\RabbitMQ
 */
class SendCompositeTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testRun(): void
    {
        $sendComposite = new SendComposite();

        $this->assertNull($sendComposite->execute(Mockery::mock(AMQPChannel::class)));

        $component = Mockery::mock(RabbitMQComponentAbstract::class);
        $component->shouldReceive('execute')->once()->withArgs([AMQPChannel::class]);
        $this->assertInstanceOf(RabbitMQCompositeAbstract::class, $sendComposite->add($component));

        $this->assertNull($sendComposite->run());
    }
}
