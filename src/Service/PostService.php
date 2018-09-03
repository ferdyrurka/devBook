<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\PostRepository;

/**
 * Class PostService
 * @package App\Service
 */
class PostService
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * PostService constructor.
     * @param PostRepository $postRepository
     */
    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @return array
     */
    public function getPostsList(): array
    {
        $posts = $this->postRepository->findAll();

        if (is_null($posts)) {
            return [
                'posts' => null
            ];
        }

        $postsArray = [];
        $i = 0;

        foreach ($posts as $post) {
            $user = $post->getUserReferences();

            $postsArray['posts'][$i]['author'] = $user->getFirstName() . ' ' . $user->getSurname();
            $postsArray['posts'][$i]['content'] = $post->getContent();

            ++$i;
        }

        return $postsArray;
    }
}
