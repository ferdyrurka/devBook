<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\NotificationController;
use App\Exception\UserNotFoundException;
use App\Tests\WebTestCase;
use \Mockery;
use Symfony\Component\HttpFoundation\Response;

class NotificationControllerTest extends WebTestCase
{
    private $guess;
    private $user;

    public function setUp()
    {
        $this->guess = $this->createClientGuess();
        $this->user = $this->createClientUser();
        parent::setUp();
    }

    public function testPermission(): void
    {
        $this->guess->request('GET', '/notifications');
        $this->assertEquals(Response::HTTP_FOUND, $this->guess->getResponse()->getStatusCode());
    }

    public function testIndexAction(): void
    {
        $this->user->request('GET', '/notifications');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }

    public function testUserNotFoundException(): void
    {
        $notificationController = Mockery::mock(NotificationController::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $notificationController->shouldReceive('getUser')->once()->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $notificationController->indexAction();
    }
}

