<?php

namespace Nin\ProPhp\Blog\Commands;

use Nin\ProPhp\Blog\Exceptions\ArgumentsException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Blog\Exceptions\CommandException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Psr\Log\LoggerInterface;

class CreateUserCommand
{
    // Команда зависит от контракта репозитория пользователей,
    // а не от конкретной реализации
    public function __construct(
        private IUsersRepository $usersRepository,
        private LoggerInterface  $logger)
    {
    }

    /**
     * @throws ArgumentsException
     * @throws InvalidArgumentException
     * @throws CommandException
     */
    public function handle(Arguments $arguments): void
    {
        $this->logger->info("Create user command started");

        $username = $arguments->get('username');
        if ($this->userExists($username)) {
            $this->logger->warning("User already exists: $username");
            throw new CommandException("User already exists: $username");
        }
        $uuid = UUID::random();
        $this->usersRepository->save(new User(
            $uuid,
            $username,
            new Name($arguments->get('first_name'), $arguments->get('last_name'))
        ));
        $this->logger->info("User created: $uuid");
    }

    private function userExists(string $username): bool
    {
        try {
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}