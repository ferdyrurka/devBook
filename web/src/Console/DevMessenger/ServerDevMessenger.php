<?php
declare(strict_types=1);

namespace App\Console\DevMessenger;

use App\Service\DevMessengerService;
use App\Service\RedisService;
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
     * @param RedisService $redisService
     */
    public function __construct(DevMessengerService $devMessengerService, RedisService $redisService)
    {
        //Clear online users
        $redisService->setDatabase(0)->flushdb();
        $redisService->setDatabase(1)->flushdb();

        $this->devMessengerService = $devMessengerService;
        parent::__construct();
    }


    public function configure(): void
    {
        $this->setName('DevMessenger:server-start');
        $this->setDescription('This command is used for managed DevMessenger WebSocket.');
        $this->setHelp(
            "
            Commands: \n
                server-start -- starting server. \n
            "
        );
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
            '2013'
        );

        $output->writeln('Server starting in port :2013...');

        $server->run();
    }
}
