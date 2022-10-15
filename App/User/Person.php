<?php

namespace App\User;

class Person
{

    public function __construct(private Name $name)
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }

}