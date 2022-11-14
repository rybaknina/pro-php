<?php

namespace Nin\ProPhp\Blog\Repositories\CommentsRepository;

use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\UUID;

interface ICommentsRepository
{
    public function save(Comment $comment): void;
    public function get(UUID $uuid): Comment;
}