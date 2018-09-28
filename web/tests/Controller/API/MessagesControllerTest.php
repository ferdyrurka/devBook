<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Controller\API\MessagesController;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MessagesControllerTest
 * @package App\Tests\Command\API
 */
class MessagesControllerTest extends WebTestCase
{
    private $guess;
    private $user;

    public function setUp(): void
    {
        $this->guess = $this->createClientGuess();
        $this->user = $this->createClientUser();
        parent::setUp();
    }

    public function testPermission():void
    {
        $this->guess->request(
            'GET',
            '/api/get-messages/3f5b1ef6-0963-404a-ad48-f3fc730b8e4a'
        );
        $this->assertEquals(Response::HTTP_OK, $this->guess->getResponse()->getStatusCode());
    }

    public function testGetMessagesAction()
    {
        $this->user->request(
            'GET',
            '/api/get-messages/3f5b1ef6-0963-404a-ad48-f3fc730b8e4a'
        );
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }
}