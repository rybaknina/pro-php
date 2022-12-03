<?php

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nin\ProPhp\Blog\Container\DIContainer;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\ICommentsRepository;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\SqliteLikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Http\Auth\AuthenticationInterface;
use Nin\ProPhp\Http\Auth\BearerTokenAuthentication;
use Nin\ProPhp\Http\Auth\JsonBodyUuidAuthentication;
use Nin\ProPhp\Http\Auth\PasswordAuthentication;
use Nin\ProPhp\Http\Auth\PasswordAuthenticationInterface;
use Nin\ProPhp\Http\Auth\TokenAuthenticationInterface;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/vendor/autoload.php';
Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
);

$container->bind(
    AuthenticationInterface::class,
    JsonBodyUuidAuthentication::class
);

$container->bind(
    IPostsRepository::class,
    SqlitePostsRepository::class
);

$container->bind(
    IUsersRepository::class,
    SqliteUsersRepository::class
);

$container->bind(
    ICommentsRepository::class,
    SqliteCommentsRepository::class
);

$container->bind(
    ILikePostsRepository::class,
    SqliteLikePostsRepository::class
);

$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);

$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);

$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);

$logger = (new Logger('blog'));
// Включаем логирование в файлы,
// если переменная окружения LOG_TO_FILES
// содержит значение 'yes'
if ('yes' === $_SERVER['LOG_TO_FILES']) {
    $logger->pushHandler(new StreamHandler(
        __DIR__ . '/logs/blog.log'
    ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
}
// Включаем логирование в консоль,
// если переменная окружения LOG_TO_CONSOLE
// содержит значение 'yes'
if ('yes' === $_SERVER['LOG_TO_CONSOLE']) {
    $logger
        ->pushHandler(
            new StreamHandler("php://stdout")
        );
}
$container->bind(
    LoggerInterface::class,
    $logger
);

return $container;