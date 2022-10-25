<?php

use Nin\ProPhp\Blog\Exceptions\AppException;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Http\Actions\Comments\CreateComment;
use Nin\ProPhp\Http\Actions\Posts\CreatePost;
use Nin\ProPhp\Http\Actions\Posts\DeletePost;
use Nin\ProPhp\Http\Actions\Posts\FindByUuid;
use Nin\ProPhp\Http\Actions\Users\FindByUsername;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;

require_once __DIR__ . '/vendor/autoload.php';

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);
try {
    $path = $request->path();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}
try {
    // Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException) {
    // Возвращаем неудачный ответ,
    // если по какой-то причине
    // не можем получить метод
    (new ErrorResponse)->send();
    return;
}
$routes = [
    // Добавили ещё один уровень вложенности
    // для отделения маршрутов,
    // применяемых к запросам с разными методами
    'GET' => [
        '/users/show' => new FindByUsername(
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite'))
        ),
        '/posts/show' => new FindByUuid(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite'),
                new SqliteUsersRepository(
                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite'))
            )
        ),
    ],
    'POST' => [
        // Добавили новый маршрут
        '/posts/create' => new CreatePost(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite'),
                new SqliteUsersRepository(
                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
                )
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
        '/comments/create' => new CreateComment(
            new SqliteCommentsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite'),
                new SqlitePostsRepository(
                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite'),
                    new SqliteUsersRepository(
                        new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
                    )
                ),
                new SqliteUsersRepository(
                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
                )
            ),
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite'),
                new SqliteUsersRepository(
                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
                )
            ),
            new SqliteUsersRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
            )
        ),
    ],
    'DELETE' => [
        '/posts' => new DeletePost(
            new SqlitePostsRepository(
                new PDO('sqlite:' . __DIR__ . '/blog.sqlite'),
                new SqliteUsersRepository(
                    new PDO('sqlite:' . __DIR__ . '/blog.sqlite'))
            )
        ),
    ]
];
// Если у нас нет маршрутов для метода запроса -
// возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Not found'))->send();
    return;
}
// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}
// Выбираем действие по методу и пути
$action = $routes[$method][$path];
try {
    $response = $action->handle($request);
} catch (AppException $e) {
    (new ErrorResponse($e->getMessage()))->send();
}
$response->send();