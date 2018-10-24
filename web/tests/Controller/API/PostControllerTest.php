<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Command\API\AddPostToQueueCommand;
use App\Controller\API\PostController;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use App\Tests\WebTestCase;
use \Mockery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostControllerTest
 * @package App\Tests\Controller\API
 */
class PostControllerTest extends WebTestCase
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
        $this->guess->request('GET', '/api/posts');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->guess->getResponse()->getStatusCode());

        $this->guess->request('POST', '/add-post', ['content' => 'Hello World']);
        $this->assertEquals(Response::HTTP_FOUND, $this->guess->getResponse()->getStatusCode());
    }

    public function testPostsListAction(): void
    {
        $this->user->request('GET', '/api/posts');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }

    public function testAddPost(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(1);

        $postController = Mockery::mock(PostController::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        //There because one successful second empty content and last not authoryzation user
        $postController->shouldReceive('getUser')->times(3)->andReturn($user, $user, null);

        $addPostToQueueCommand = Mockery::mock(AddPostToQueueCommand::class);
        $addPostToQueueCommand->shouldReceive('setUserId')->withArgs([1])->once();
        $addPostToQueueCommand->shouldReceive('setContent')->withArgs(['contentValue'])->once();

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')->withArgs(['content'])->andReturn('contentValue', null)->times(2);

        $commandService = Mockery::mock(CommandService::class);
        $commandService->shouldReceive('setCommand')->withArgs([AddPostToQueueCommand::class])->once();
        $commandService->shouldReceive('execute')->once();

        $result = $postController->addPost($request, $commandService, $addPostToQueueCommand);
        $this->assertInstanceOf(JsonResponse::class, $result);

        $result = $postController->addPost($request, $commandService, $addPostToQueueCommand);
        $this->assertInstanceOf(JsonResponse::class, $result);

        $this->expectException(UserNotFoundException::class);
        $postController->addPost($request, $commandService, $addPostToQueueCommand);
    }
}
