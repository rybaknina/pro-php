<?php

namespace Nin\ProPhp\Http\Actions\Posts;

use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;

class DeletePost implements ActionInterface
{
    public function __construct(
        private IPostsRepository $postsRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $uuid = new UUID($request->query('uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $this->postsRepository->delete($uuid);
        return new SuccessfulResponse([
            'uuid' => (string)$uuid,
        ]);
    }

}