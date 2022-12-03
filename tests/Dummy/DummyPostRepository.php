<?php

namespace Tests\Dummy;

use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\UUID;

class DummyPostRepository
{
    public function postsRepository(array $posts): IPostsRepository
    {
        return new class($posts) implements IPostsRepository {
            public function __construct(
                private array $posts
            )
            {
            }

            public function save(Post $post): void
            {
            }

            public function get(UUID $uuid): Post
            {
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && $uuid == $post->uuid()) {
                        return $post;
                    }
                }
                throw new PostNotFoundException("Not found");
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }
}