<?php

namespace Nin\ProPhp\Http\Actions\Users;

use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;

class CreateUser implements ActionInterface
{
    public function __construct(
        private IUsersRepository $usersRepository,
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $user = User::createFrom(
                $request->jsonBodyField('username'),
                $request->jsonBodyField('password'),
                new Name(
                    $request->jsonBodyField('first_name'),
                    $request->jsonBodyField('last_name')
                )
            );
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->usersRepository->save($user);

        return new SuccessfulResponse([
            'uuid' => (string)$user->uuid(),
        ]);
    }
}