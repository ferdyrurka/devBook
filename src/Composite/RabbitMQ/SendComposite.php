<?php
declare(strict_types=1);

namespace App\Composite\RabbitMQ;

/**
 * Class SendComposite
 * @package App\Composite\RabbitMQ
 */
class SendComposite extends RabbitMQCompositeAbstract
{

    private $send = [];

    /**
     *
     */
    public function run(): void
    {
        foreach ($this->send as $send) {
            $send->execute($this->channel);
        }

        $this->close();
    }

    /**
     * @param RabbitMQComponentAbstract $componentAbstract
     * @return RabbitMQCompositeAbstract
     */
    public function add(RabbitMQComponentAbstract $componentAbstract): RabbitMQCompositeAbstract
    {
        $this->send[] = $componentAbstract;

        return $this;
    }
}
