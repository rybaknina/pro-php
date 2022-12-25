<?php

namespace Tests\Commands;

use Nin\ProPhp\Blog\Commands\Arguments;
use Nin\ProPhp\Blog\Commands\CreateUserCommand;
use Nin\ProPhp\Blog\Exceptions\ArgumentsException;
use Nin\ProPhp\Blog\Exceptions\CommandException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\Commands\Users\CreateUser;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\Dummy\DummyLogger;
use Tests\Dummy\DummyUserRepository;

class CreateUserCommandTest extends TestCase
{
    private DummyUserRepository $dummyUserRepository;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->dummyUserRepository = new DummyUserRepository();
    }
    // Тест проверяет, что команда действительно требует фамилию пользователя
    /**
     */
    public function testItRequiresLastName(): void
    {
        // Тестируем новую команду
        $command = new CreateUser(
            $this->usersRepository([])
        );

        // Меняем тип ожидаемого исключения ..
        $this->expectException(RuntimeException::class);
        // .. и его сообщение
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "last_name").'
        );

        // Запускаем команду методом run вместо handle
        $command->run(
        // Передаём аргументы как ArrayInput,
        // а не Arguments
        // Сами аргументы не меняются
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',

            ]),
            // Передаём также объект,
            // реализующий контракт OutputInterface
            // Нам подойдёт реализация,
            // которая ничего не делает
            new NullOutput()
        );
    }

    // Тем же образом обновляем остальные тесты
    public function testItRequiresPassword(): void
    {
        $command = new CreateUser(
            $this->usersRepository([])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "first_name, last_name, password").'
        );

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
            ]),
            new NullOutput()
        );
    }

    public function testItRequiresFirstName(): void
    {
        $command = new CreateUser(
            $this->usersRepository([])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Not enough arguments (missing: "first_name, last_name").'
        );

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
            ]),
            new NullOutput()
        );
    }

    // Тест, проверяющий, что команда сохраняет пользователя в репозитории
    public function testItSavesUserToRepository(): void
    {
        $usersRepository = $this->usersRepository([]);
        $command = new CreateUser(
            $usersRepository
        );

        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]),
            new NullOutput()
        );

        $this->assertTrue($usersRepository->wasCalled());
    }

    /**
     * @throws InvalidArgumentException
     * @throws ArgumentsException
     */
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        $user = new User(UUID::random(), "user123", new Name("first", "last"), 'password' );
        $command = new CreateUserCommand($this->usersRepository([$user]), new DummyLogger());
        // Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);

        // и его сообщение
        $this->expectExceptionMessage('User already exists: user123');

        // Запускаем команду с аргументами
        $command->handle(new Arguments(['username' => 'user123']));
    }

    private function usersRepository(array $users): IUsersRepository
    {
        return $this->dummyUserRepository->usersRepository($users);
    }
}