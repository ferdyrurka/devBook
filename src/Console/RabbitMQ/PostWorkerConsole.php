<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ;

use App\Command\Console\AddPostCommand;
use App\Service\RabbitMQConnectService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PostWorkerConsole
 * @package App\Console\RabbitMQ
 */
class PostWorkerConsole extends Command
{
    /**
     * @var AddPostCommand
     */
    private $addPostCommand;

    /**
     * PostWorkerConsole constructor.
     * @param AddPostCommand $addPostCommand
     */
    public function __construct(AddPostCommand $addPostCommand)
    {
        $this->addPostCommand = $addPostCommand;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('RabbitMQ:Post-work');
        $this->setDescription('This script tracks a RabbitMQ Queue name post');
        $this->setHelp('During writing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $service = new RabbitMQConnectService();
        $channel = $service->getChannel();

        $channel->queue_declare('post', false, false, false);

        $channel->basic_consume('post', false, false, false, [$this->addPostCommand, 'execute']);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $service->close($channel);

        return;
    }
}
