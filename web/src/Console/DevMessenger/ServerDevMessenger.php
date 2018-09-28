<?php
declare(strict_types=1);

namespace App\Console\DevMessenger;

use App\Service\DevMessengerService;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ServerDevMessenger
 * @package App\Console\DevMessenger
 */
class ServerDevMessenger extends Command
{

    /**
     * @var DevMessengerService
     */
    private $devMessengerService;

    /**
     * ServerDevMessenger constructor.
     * @param DevMessengerService $devMessengerService
     */
    public function __construct(DevMessengerService $devMessengerService)
    {
        $this->devMessengerService = $devMessengerService;
        parent::__construct();
    }


    public function configure(): void
    {
        $this->setName('DevMessage:server');
        $this->setDescription('');
        $this->setHelp('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->devMessengerService
                )
            ),
            '2013',
            '127.0.0.6'
        );

        $output->writeln('Server starting...');

        $server->run();
    }
}
