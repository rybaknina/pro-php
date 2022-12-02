<?php

use Nin\ProPhp\Blog\Exceptions\AppException;
use Nin\ProPhp\Http\Actions\Comments\CreateComment;
use Nin\ProPhp\Http\Actions\Likes\CreateLikePost;
use Nin\ProPhp\Http\Actions\Likes\FindAllByPostUuid;
use Nin\ProPhp\Http\Actions\Posts\CreatePost;
use Nin\ProPhp\Http\Actions\Posts\DeletePost;
use Nin\ProPhp\Http\Actions\Posts\FindByUuid;
use Nin\ProPhp\Http\Actions\Users\FindByUsername;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input'),
);
$logger = $container->get(LoggerInterface::class);

try {
    $path = $request->path();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}
try {
    // Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
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
        '/users/show' => FindByUsername::class,
        '/posts/show' => FindByUuid::class,
        '/likes/post' => FindAllByPostUuid::class
    ],
    'POST' => [
        // Добавили новый маршрут
        '/posts/create' => CreatePost::class,
        '/comments/create' => CreateComment::class,
        '/likes/post/create' => CreateLikePost::class,
    ],
    'DELETE' => [
        '/posts' => DeletePost::class
    ]
];
// Если у нас нет маршрутов для метода запроса -
// возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)
    || !array_key_exists($path, $routes[$method])) {
// Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];
try {
    $action = $container->get($actionClassName);
    $response = $action->handle($request);
} catch (Exception $e) {
    // Логируем сообщение с уровнем ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
    // Больше не отправляем пользователю
    // конкретное сообщение об ошибке,
    // а только логируем его
    (new ErrorResponse)->send();
    return;
}
$response->send();