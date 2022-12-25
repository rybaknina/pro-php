<?php

namespace Nin\ProPhp\Http\Actions\Auth;

use DateTimeImmutable;
use DateTimeInterface;
use Nin\ProPhp\Blog\Exceptions\AuthException;
use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokenNotFoundException;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class LogOut implements ActionInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository,
        private LoggerInterface               $logger
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $header = $request->header('Authorization');
            // Проверяем, что заголовок имеет правильный формат
            if (!str_starts_with($header, self::HEADER_PREFIX)) {
                throw new AuthException("Malformed token: [$header]");
            }
            // Отрезаем префикс Bearer
            $token = mb_substr($header, strlen(self::HEADER_PREFIX));

            // Ищем токен в репозитории
            try {
                $authToken = $this->authTokensRepository->get($token);
            } catch (AuthTokenNotFoundException $e) {
                throw new AuthException("Bad token: [$token]");
            }

        } catch (HttpException | AuthException | AuthTokenNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $authToken->setExpiresOn(new DateTimeImmutable());
        $this->authTokensRepository->save($authToken);
        $this->logger->info("Token expired: " . $authToken->expiresOn()->format(DateTimeInterface::ATOM));

        return new SuccessfulResponse([
            'expiresOn' => $authToken->expiresOn()->format('Y-m-d H:i:s'),
        ]);
    }
}