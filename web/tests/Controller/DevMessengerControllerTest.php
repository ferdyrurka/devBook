<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\DevMessengerController;
use App\Exception\UserNotFoundException;
use App\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use \Mockery;

/**
 * Class DevMessengerControllerTest
 * @package App\Tests\Controller
 */
class DevMessengerControllerTest extends WebTestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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

    public function testDevMessenger(): void
    {
        $this->user->request('GET', '/dev-messenger');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }

    /**
     * This tests is checking if getUser return null
     */
    public function testUserNotFound(): void
    {
        $devMessengerController = Mockery::mock(DevMessengerController::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $devMessengerController->shouldReceive('getUser')->once()->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $devMessengerController->indexAction();
    }
}
