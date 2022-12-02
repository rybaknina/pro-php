<?php

namespace Tests\Commands;

use Nin\ProPhp\Blog\Commands\Arguments;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Blog\Commands\CreateUserCommand;
use Nin\ProPhp\Blog\Exceptions\ArgumentsException;
use Nin\ProPhp\Blog\Exceptions\CommandException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use PHPUnit\Framework\TestCase;
use Tests\Dummy\DummyLogger;

class CreateUserCommandTest extends TestCase
{
    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): IUsersRepository
    {
        return new class implements IUsersRepository {
            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }

    // Тест проверяет, что команда действительно требует фамилию пользователя
    /**
     * @throws InvalidArgumentException
     * @throws CommandException
     */
    public function testItRequiresLastName(): void
    {
        // Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
            // Нам нужно передать имя пользователя,
            // чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
        ]));
    }

    // Тест проверяет, что команда действительно требует имя пользователя
    /**
     * @throws InvalidArgumentException
     * @throws CommandException
     */
    public function testItRequiresFirstName(): void
    {
        // Вызываем ту же функцию
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');
        $command->handle(new Arguments(['username' => 'Ivan']));
    }
}