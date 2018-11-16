<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Command\API\SearchFriendsCommand;
use App\Controller\API\FriendsController;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use App\Tests\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Mockery;

/**
 * Class FriendsControllerTest
 * @package App\Tests\Command\API
 */
class FriendsControllerTest extends WebTestCase
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
        $this->guess->request('GET', '/api/search-friends?q=Lore');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->guess->getResponse()->getStatusCode());
    }

    public function testSearchFriendsAction(): void
    {
        $this->user->request('GET', '/api/search-friends?q=Lore');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }

    public function testInvalidArguments(): void
    {
        $this->user->request('GET', '/api/search-friends');
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->user->getResponse()->getStatusCode());
    }

    public function testUserNotFoundException(): void
    {
        $friendsController = Mockery::mock(FriendsController::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $friendsController->shouldReceive('getUser')->andReturnNull()->once();

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')->withArgs(['q'])->andReturn('q');

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->expectException(UserNotFoundException::class);
        $friendsController->searchFriendsAction(
            Mockery::mock(CommandService::class),
            $request,
            $eventDispatcher
        );
    }

    public function testSearchUser(): void
    {
        $this->user->request('GET', '/api/search-friends?q=Admin');
        $this->assertEmpty(str_replace('[]', '', $this->user->getResponse()->getContent()));

        $this->user->request('GET', '/api/search-friends?q=User');
        $this->assertNotNull($this->user->getResponse()->getContent());
    }
}
