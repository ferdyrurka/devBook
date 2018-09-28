<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeControllerTest
 * @package App\Tests\Controller
 */
class HomeControllerTest extends WebTestCase
{
    private $guess;
    private $user;

    public function setUp(): void
    {
        $this->guess = $this->createClientGuess();
        $this->user = $this->createClientUser();
        parent::setUp();
    }

    public function testPermission()
    {
        $this->user->request('GET', '/');
        $this->assertEquals(Response::HTTP_FOUND, $this->user->getResponse()->getStatusCode());
    }

    public function testIndexAction()
    {
        $this->guess->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK, $this->guess->getResponse()->getStatusCode());
    }
}
