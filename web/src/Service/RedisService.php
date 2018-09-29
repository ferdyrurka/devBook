<?php
declare(strict_types=1);

namespace App\Service;

use Predis\Client;

/**
 * Class ReddisService
 * @package App\Service
 */
class RedisService
{
    private $client;

    /**
     * ReddisService constructor.
     * @param int $database
     */
    public function __construct(int $database)
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379
        ]);

        $this->client->auth('my-pass');

        $this->setDatabase($database);
    }

    /**
     * @param int $database
     * @return Client
     */
    public function setDatabase(int $database): Client
    {
        $this->client->select($database);

        return $this->client;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
