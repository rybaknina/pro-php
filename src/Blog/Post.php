<?php

namespace Nin\ProPhp\Blog;

class Post
{
    /**
     * @param UUID $uuid
     * @param UUID $userUuid
     * @param string $title
     * @param string $text
     */
    public function __construct(private UUID $uuid, private UUID $userUuid, private string $title, private string $text)
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
     * @return UUID
     */
    public function userUuid(): UUID
    {
        return $this->userUuid;
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
        return 'user c uuid ' . $this->userUuid . ' пишет: ' . $this->text;
    }
}