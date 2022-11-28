<?php

use Nin\ProPhp\Blog\Container\DIContainer;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\ICommentsRepository;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\SqliteLikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;

require_once __DIR__ . '/vendor/autoload.php';

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
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

return $container;