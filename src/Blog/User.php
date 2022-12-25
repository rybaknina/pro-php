<?php

namespace Nin\ProPhp\Blog;

class User
{
    /**
     * @param UUID $uuid
     * @param string $username
     * @param Name $name
     * @param string $hashedPassword
     */
    public function __construct(
        private UUID   $uuid,
        private string $username,
        private Name   $name,
        private string $hashedPassword
    )
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

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    private static function hash(string $password, UUID $uuid): string
    {
        return hash('sha256', $uuid . $password);
    }

    public function checkPassword(string $password): bool
    {
        return $this->hashedPassword === self::hash($password, $this->uuid);
    }

    /**
     * @throws Exceptions\InvalidArgumentException
     */
    public static function createFrom(
        string $username,
        string $password,
        Name   $name
    ): self
    {
        $uuid = UUID::random();
        return new self(
            $uuid,
            $username,
            $name,
            self::hash($password, $uuid)
        );
    }

    public function __toString(): string
    {
        return $this->username . ': ' . $this->name;
    }
}