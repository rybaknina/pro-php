<?php

namespace Nin\ProPhp\Http\Auth;

use Nin\ProPhp\Blog\Exceptions\AuthException;
use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Http\Request;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
    public function __construct(
        private IUsersRepository $usersRepository
    )
    {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            // Получаем имя пользователя из JSON-тела запроса;
            // ожидаем, что имя пользователя находится в поле username
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            // Если невозможно получить имя пользователя из запроса -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }
        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            // Если пользователь не найден -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }
    }
}