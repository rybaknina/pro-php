<?php

namespace Nin\ProPhp\Http\Actions\Likes;

use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;

class FindAllByPostUuid implements ActionInterface
{
    public function __construct(
        private ILikePostsRepository $likePostsRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $postUuid = new UUID($request->query('uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $likesByPostUuid = $this->likePostsRepository->getByPostUuid($postUuid);
        $likes = [];
        foreach ($likesByPostUuid as $like) {
            $likes[] = (object)[
                'uuid' => (string)$like->uuid(),
                'post_uuid' => (string)$like->post()->uuid(),
                'user_uuid' => (string)$like->user()->uuid()
            ];
        }
        // Возвращаем успешный ответ
        return new SuccessfulResponse($likes);
    }
}