<?php

use App\Blog\Post;
use App\User\Person;
use App\User\Name;

/**
 * @param string $file
 * @return string
 */
function fileWithoutUnderscore(string $file): string
{
    $array = explode(DIRECTORY_SEPARATOR, $file);
    if (str_contains(end($array), '_')) {
        $rightPath = str_replace('_', DIRECTORY_SEPARATOR, array_pop($array));
        array_push($array, $rightPath);
        return implode(DIRECTORY_SEPARATOR, $array);
    }
    return $file;
}

spl_autoload_register(function ($class) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists(fileWithoutUnderscore($file))) {
        var_dump($file) . PHP_EOL;
        require_once $file;
    }
});

$post = new Post(
    new Person(
        new Name('Иван', 'Никитин')
    ),
    'Всем привет!'
);
print $post;

$array = array(
    str_replace('\\', DIRECTORY_SEPARATOR, "\Doctrine\Common\ClassLoader" . '.php'),
    str_replace('\\', DIRECTORY_SEPARATOR, "\my\package\Class_Name" . '.php'),
    str_replace('\\', DIRECTORY_SEPARATOR, "\my\package_name\Class_Name" . '.php'),
    str_replace('\\', DIRECTORY_SEPARATOR, "\my\package_name\ClassName" . '.php')
);

foreach ($array as $key => $value) {
    var_dump(fileWithoutUnderscore($value)) . PHP_EOL;
}
