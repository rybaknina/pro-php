<?php

namespace Nin\ProPhp\Blog\Repositories\UsersRepository;

use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;

interface IUsersRepository
{
    public function save(User $user): void;
    public function get(UUID $uuid): User;
    public function getByUsername(string $username): User;
}