<?php

namespace Nin\ProPhp\Http\Actions\Posts;

use Nin\ProPhp\Blog\Exceptions\AuthException;
use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\Auth\TokenAuthenticationInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreatePost implements ActionInterface
{
    // Внедряем репозитории статей и пользователей
    public function __construct(
        private IPostsRepository             $postsRepository,
        private TokenAuthenticationInterface $authentication,
        private LoggerInterface              $logger
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        // Пытаемся создать UUID пользователя из данных запроса
        try {
            $user = $this->authentication->user($request);
        } catch (HttpException | InvalidArgumentException | AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $newPostUuid = UUID::random();
        try {
            // Пытаемся создать объект статьи
            // из данных запроса
            $post = new Post(
                $newPostUuid,
                $user,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        // Сохраняем новую статью в репозитории
        $this->postsRepository->save($post);
        $this->logger->info("Post created: $newPostUuid");
        // Возвращаем успешный ответ,
        // содержащий UUID новой статьи
        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}