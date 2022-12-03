<?php

namespace Tests\Dummy;

use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;

class DummyUserRepository
{
    public function usersRepository(array $users): IUsersRepository
    {
        return new class($users) implements IUsersRepository {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }
}