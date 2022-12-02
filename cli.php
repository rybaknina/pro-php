<?php

use Nin\ProPhp\Blog\Commands\Arguments;
use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\Commands\CreateUserCommand;
use Nin\ProPhp\Blog\Exceptions\AppException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\SqliteLikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$faker = Faker\Factory::create('ru_RU');
///Создаём объект подключения к SQLite
$connection = new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH']);
$command = $container->get(CreateUserCommand::class);
$logger = $container->get(LoggerInterface::class);
// In-memory-репозиторий тоже подойдёт
// $usersRepository = new InMemoryUsersRepository();
//Создаём объект репозитория
$usersRepository = $container->get(SqliteUsersRepository::class);
$postsRepository = $container->get(SqlitePostsRepository::class);
$commentsRepository = $container->get(SqliteCommentsRepository::class);
$likesPostRepository = $container->get(SqliteLikePostsRepository::class);
//try {
//    // "Заворачиваем" $argv в объект типа Arguments
//    $command->handle(Arguments::fromArgv($argv));
//} catch (AppException $e) {
//    $logger->error($e->getMessage(), ['exception' => $e]);
//}
//Добавляем в репозиторий
//
try {
    $userUuid = UUID::random();
    $postUuid = UUID::random();
    $commentUuid = UUID::random();
    $likePostUuid = UUID::random();
    $user = new User($userUuid, $faker->userName, new Name($faker->firstName, $faker->lastName()));
    $usersRepository->save($user);
    $userUuid1 = UUID::random();
    $user1 = new User($userUuid1, $faker->userName, new Name($faker->firstName, $faker->lastName()));
    $usersRepository->save($user1);
    $post = new Post($postUuid, $user, $faker->jobTitle, $faker->realText(50));
    $postsRepository->save($post);
    $comment = new Comment($commentUuid, $post, $user, $faker->realText(100));
    $commentsRepository->save($comment);
    $likePost = new LikePost($likePostUuid, $post, $user);
    $likesPostRepository->save($likePost);
    // can be UNIQUE constraint failed: likes_post.post_uuid, likes_post.user_uuid
    $likePost2 = new LikePost(UUID::random(), $post, $user1);
    $likesPostRepository->save($likePost2);

    print $usersRepository->get($userUuid) . PHP_EOL;
    print $postsRepository->get($postUuid) . PHP_EOL;
    print $commentsRepository->get($commentUuid) . PHP_EOL;
    $byPostUuid = $likesPostRepository->getByPostUuid($postUuid);
    foreach ($byPostUuid as $value) {
        echo $value->__toString() . PHP_EOL;
    }
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}