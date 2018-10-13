<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Controller\API\ConversationController;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConversationControllerTest
 * @package App\Tests\Controller\API
 */
class ConversationControllerTest extends WebTestCase
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
        $this->guess->request('GET', '/api/get-conversation-list');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->guess->getResponse()->getStatusCode());
    }

    public function testGetConversationListAction(): void
    {
        $this->user->request('GET', '/api/get-conversation-list');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }
}
