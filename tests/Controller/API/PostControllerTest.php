<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Controller\API\PostController;
use App\Tests\WebTestCase;
use \Mockery;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostControllerTest
 * @package App\Tests\Controller\API
 */
class PostControllerTest extends WebTestCase
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
        $this->guess->request('GET', '/api/posts');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->guess->getRespone()->getStatusCode());

        $this->guess->request('POST', '/add-post', ['content' => 'Hello World']);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->guess->getRespone()->getStatusCode());
    }

    public function testPostsListAction()
    {
        $this->user->request('GET', '/api/posts');
        $this->assertEquals(Response::HTTP_OK, $this->user->getRespone()->getStatusCode());
    }

    public function testAddPost()
    {

    }
}
