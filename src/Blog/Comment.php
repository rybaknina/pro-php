<?php

namespace Nin\ProPhp\Blog;

class Comment
{

    /**
     * @param UUID $uuid
     * @param UUID $postUuid
     * @param UUID $userUuid
     * @param string $text
     */
    public function __construct(private UUID $uuid, private UUID $postUuid, private UUID $userUuid, private string $text)
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
    public function postUuid(): UUID
    {
        return $this->postUuid;
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
    public function text(): string
    {
        return $this->text;
    }

    public function __toString(): string
    {
        return 'на пост с uuid ' . $this->postUuid . ' user с uuid ' . $this->userUuid . ' пишет коммент ' . $this->text;
    }

}