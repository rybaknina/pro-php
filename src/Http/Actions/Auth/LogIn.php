<?php

namespace Nin\ProPhp\Http\Actions\Auth;

use DateTimeImmutable;
use Nin\ProPhp\Blog\AuthToken;
use Nin\ProPhp\Blog\Exceptions\AuthException;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\Auth\PasswordAuthenticationInterface;
use Nin\ProPhp\http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\SuccessfulResponse;

class LogIn implements ActionInterface
{
    public function __construct(
        // Авторизация по паролю
        private PasswordAuthenticationInterface $passwordAuthentication,
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        // Аутентифицируем пользователя
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Генерируем токен
        $authToken = new AuthToken(
        // Случайная строка длиной 40 символов
            bin2hex(random_bytes(40)),
            $user->uuid(),
            // Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );

        // Сохраняем токен в репозиторий
        $this->authTokensRepository->save($authToken);

        // Возвращаем токен
        return new SuccessfulResponse([
            'token' => $authToken->token(),
        ]);
    }

}