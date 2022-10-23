<?php

use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\Commands\CreateUserCommand;
use Nin\ProPhp\Blog\Exceptions\AppException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;

require_once __DIR__ . '/vendor/autoload.php';

$faker = Faker\Factory::create('ru_RU');
///Создаём объект подключения к SQLite
$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

// In-memory-репозиторий тоже подойдёт
// $usersRepository = new InMemoryUsersRepository();
//Создаём объект репозитория
$usersRepository = new SqliteUsersRepository($connection);
$postsRepository = new SqlitePostsRepository($connection, $usersRepository);
$commentsRepository = new SqliteCommentsRepository($connection, $postsRepository, $usersRepository);
$command = new CreateUserCommand($usersRepository);

//try {
//// "Заворачиваем" $argv в объект типа Arguments
//    $command->handle(Arguments::fromArgv($argv));
//} catch (AppException $e) {
//    echo "{$e->getMessage()}\n";
//}
//Добавляем в репозиторий

try {
    $userUuid = UUID::random();
    $postUuid = UUID::random();
    $commentUuid = UUID::random();
    $user = new User($userUuid, $faker->userName, new Name($faker->firstName, $faker->lastName()));
    $usersRepository->save($user);
    $post = new Post($postUuid, $user, $faker->jobTitle, $faker->realText(50));
    $postsRepository->save($post);
    $comment = new Comment($commentUuid, $post, $user, $faker->realText(100));
    $commentsRepository->save($comment);
    print $usersRepository->get($userUuid) . PHP_EOL;
    print $postsRepository->get($postUuid) . PHP_EOL;
    print $commentsRepository->get($commentUuid) . PHP_EOL;
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}