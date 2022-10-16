<?php

namespace Nin\ProPhp\Blog\Repositories\PostsRepository;

use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\UUID;

interface IPostsRepository
{
    public function save(Post $post): void;
    public function get(UUID $uuid): Post;
}