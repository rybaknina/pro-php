<?php

namespace Nin\ProPhp\Http\Auth;

use Nin\ProPhp\Blog\Exceptions\AuthException;
use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Request;

class JsonBodyUuidIdentification implements IdentificationInterface
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
            $userUuid = new UUID($request->jsonBodyField('user_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            throw new AuthException($e->getMessage());
        }

        try {
            return $this->usersRepository->get($userUuid);
        } catch (UserNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }
    }
}