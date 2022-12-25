<?php

namespace Tests\Dummy;

use Nin\ProPhp\Blog\AuthToken;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokenNotFoundException;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;

class DummyTokenRepository
{
    public function tokensRepository(array $tokens): AuthTokensRepositoryInterface
    {
        return new class($tokens) implements AuthTokensRepositoryInterface {
            public function __construct(
                private array $tokens
            )
            {
            }

            public function save(AuthToken $authToken): void
            {
            }

            public function get(string $token): AuthToken
            {
                foreach ($this->tokens as $authToken) {
                    if ($authToken instanceof AuthToken && $authToken->token() == $token) {
                        return $authToken;
                    }
                }
                throw new AuthTokenNotFoundException("Cannot find token: $token");
            }
        };
    }
}