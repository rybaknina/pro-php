<?php

namespace Nin\ProPhp\Blog;

class User
{
    /**
     * @param UUID $uuid
     * @param string $username
     * @param Name $name
     */
    public function __construct(private UUID $uuid, private string $username, private Name $name)
    {
    }

    /**
     * @return string
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->username . ': ' . $this->name;
    }
}