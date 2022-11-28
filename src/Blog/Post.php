<?php

namespace Nin\ProPhp\Blog;

class Post
{
    /**
     * @param UUID $uuid
     * @param User $user
     * @param string $title
     * @param string $text
     */
    public function __construct(private UUID $uuid, private User $user, private string $title, private string $text)
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
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    public function __toString()
    {
        return $this->user . ': ' . $this->text;
    }
}