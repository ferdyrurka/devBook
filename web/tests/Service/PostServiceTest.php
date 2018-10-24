<?php
declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\PostService;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class PostServiceTest
 * @package App\Tests\Service
 */
class PostServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $postService;
    private $postRepository;

    public function setUp(): void
    {
        $this->postRepository = Mockery::mock(PostRepository::class);

        $this->postService = new PostService($this->postRepository);
        parent::setUp();
    }

    public function testGetPostsList(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getFirstName')->once()->andReturn('Hello');
        $user->shouldReceive('getSurname')->once()->andReturn('World');

        $post = Mockery::mock(Post::class);
        $post->shouldReceive('getUserReferences')->once()->andReturn($user);
        $post->shouldReceive('getContent')->once()->andReturn('Content');

        $this->postRepository->shouldReceive('findAll')->times(2)->andReturn(null, [$post]);

        $result = $this->postService->getPostsList();
        $this->assertNull($result['posts']);

        $result = $this->postService->getPostsList();
        $this->assertEquals('Hello World', $result['posts'][0]['author']);
        $this->assertEquals('Content', $result['posts'][0]['content']);
    }
}
