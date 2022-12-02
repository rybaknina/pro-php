<?php

namespace Nin\ProPhp\Blog;

class LikePost
{
    /**
     * @param UUID $uuid
     * @param Post $post
     * @param User $user
     */
    public function __construct(private UUID $uuid, private Post $post, private User $user)
    {
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return Post
     */
    public function post(): Post
    {
        return $this->post;
    }

    /**
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }

    public function __toString(): string
    {
        return 'на пост ' . $this->post . ' ' . $this->user . ' поставил like';
    }

}