<?php
declare(strict_types=1);

namespace App\Console\RabbitMQ;

use App\Console\RabbitMQ\Command\AddPostCommand;
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
     * @var RabbitMQConnectService
     */
    private $rabbitMQConnect;

    /**
     * PostWorkerConsole constructor.
     * @param AddPostCommand $addPostCommand
     * @param RabbitMQConnectService $rabbitMQConnect
     */
    public function __construct(AddPostCommand $addPostCommand, RabbitMQConnectService $rabbitMQConnect)
    {
        $this->addPostCommand = $addPostCommand;
        $this->rabbitMQConnect = $rabbitMQConnect;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('RabbitMQ:post-worker-start');
        $this->setDescription('This script tracks a RabbitMQ Queue name post');
        $this->setHelp('During writing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $channel = $this->rabbitMQConnect->getChannel();

        $channel->queue_declare('post', false, false, false);

        $channel->basic_consume(
            'post',
            '',
            false,
            true,
            false,
            false,
            [$this->addPostCommand, 'execute']
        );

        $output->writeln('I\'m ready to working!');

        while (count($channel->callbacks)) {
            $channel->wait();

            $output->writeln('I\'m added post to database!');
        }

        $output->writeln('Connection closed, bye. :(');

        $this->rabbitMQConnect->close($channel);
    }
}
