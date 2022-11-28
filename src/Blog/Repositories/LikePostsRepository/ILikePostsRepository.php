<?php

namespace Nin\ProPhp\Blog\Repositories\LikePostsRepository;

use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\UUID;

interface ILikePostsRepository
{
    public function save(LikePost $likePost): void;
    public function getByPostUuid(UUID $postUuid): array;
}