<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\MainPageController;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MainPageControllerTest
 * @package App\Tests\Controller
 */
class MainPageControllerTest extends WebTestCase
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
        $this->guess->request('GET', '/home');
        $this->assertEquals(Response::HTTP_FOUND, $this->guess->getResponse()->getStatusCode());
    }

    public function testIndexAction(): void
    {
        $this->user->request('GET', '/home');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }
}
