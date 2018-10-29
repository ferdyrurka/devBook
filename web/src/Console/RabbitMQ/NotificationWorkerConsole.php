<?php
declare(strict_types=1);


namespace App\Console\RabbitMQ;


use App\Console\RabbitMQ\Handler\AddNotificationHandler;
use App\Service\RabbitMQConnectService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationWorkerConsole extends Command
{
    /**
     * @var AddNotificationHandler
     */
    private $addNotificationHandler;

    /**
     * @var RabbitMQConnectService
     */
    private $rabbitMQConnectService;

    public function __construct(
        AddNotificationHandler $addNotificationHandler,
        RabbitMQConnectService $rabbitMQConnectService
    ) {
        $this->addNotificationHandler = $addNotificationHandler;
        $this->rabbitMQConnectService = $rabbitMQConnectService;

        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('RabbitMQ:notification-worker-start');
        $this->setDescription('This script tracks a RabbitMQ Queue name notification');
        $this->setHelp('During writing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $this->rabbitMQConnectService->getChannel();

        $channel->queue_declare('notification', false, false, false);

        $channel->basic_consume(
            'notification',
            '',
            false,
            true,
            false,
            false,
            [$this->addNotificationHandler, 'handle']
        );

        $output->writeln('Notification worker ready to working!');

        while (count($channel->callbacks)) {
            $channel->wait();

            $output->writeln('I\'m added notification to database!');
        }

        $output->writeln('Connection closed, bye. :(');

        $this->rabbitMQConnectService->close($channel);
    }
}
