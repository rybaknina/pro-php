<?php

use Nin\ProPhp\Blog\Commands\Users\CreateUser;
use Nin\ProPhp\Blog\Commands\Users\UpdateUser;
use Nin\ProPhp\Blog\Commands\Posts\DeletePost;
use Nin\ProPhp\Blog\Commands\FakeData\PopulateDB;
use Symfony\Component\Console\Application;

$container = require __DIR__ . '/bootstrap.php';

// Создаём объект приложения
$application = new Application();

// Перечисляем классы команд
$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class,
];

foreach ($commandsClasses as $commandClass) {
    // Посредством контейнера
    // создаём объект команды
    $command = $container->get($commandClass);

    // Добавляем команду к приложению
    $application->add($command);
}

// Запускаем приложение
$application->run();