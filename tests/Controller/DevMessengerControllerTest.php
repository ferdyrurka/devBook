<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\DevMessengerController;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DevMessengerControllerTest
 * @package App\Tests\Controller
 */
class DevMessengerControllerTest extends WebTestCase
{
    private $guess;
    private $user;

    public function setUp(): void
    {
        $this->guess = $this->createClientGuess();
        $this->user = $this->createClientUser();
        parent::setUp();
    }

    public function testPermission(): void
    {
        $this->guess->request('GET', '/dev-messenger');
        $this->assertEquals(Response::HTTP_FOUND, $this->guess->getResponse()->getStatusCode());
    }

    public function testIndexAction(): void
    {
        $this->user->request('GET', '/dev-messenger');
        $this->assertEquals(Response::HTTP_FOUND, $this->user->getResponse()->getStatusCode());
    }
}
