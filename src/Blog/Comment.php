<?php

namespace Nin\ProPhp\Blog;

class Comment
{

    /**
     * @param UUID $uuid
     * @param Post $post
     * @param User $user
     * @param string $text
     */
    public function __construct(private UUID $uuid, private Post $post, private User $user, private string $text)
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

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    public function __toString(): string
    {
        return 'на пост с uuid ' . $this->post . ' user с uuid ' . $this->user . ' пишет коммент ' . $this->text;
    }

}