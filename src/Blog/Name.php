<?php

namespace Nin\ProPhp\Blog;

class Name
{

    /**
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(private string $firstName, private string $lastName)
    {
    }

    /**
     * @return string
     */
    public function first(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function last(): string
    {
        return $this->lastName;
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

}