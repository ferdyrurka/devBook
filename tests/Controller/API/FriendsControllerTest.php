<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Controller\API\FriendsController;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->guess->getResponse()->getStatusCode());
    }

    public function testSearchFriendsAction(): void
    {
        $this->user->request('GET', '/api/search-friends?q=Lore');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }
}
