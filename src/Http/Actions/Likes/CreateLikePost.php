<?php

namespace Nin\ProPhp\Http\Actions\Likes;

use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;
use PDOException;

class CreateLikePost implements ActionInterface
{
    public function __construct(
        private ILikePostsRepository $likePostsRepository,
        private IPostsRepository     $postsRepository,
        private IUsersRepository     $usersRepository,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handle(Request $request): Response
    {
        try {
            $userUuid = new UUID($request->jsonBodyField('author_uuid'));
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $user = $this->usersRepository->get($userUuid);
            $post = $this->postsRepository->get($postUuid);
        } catch (UserNotFoundException | PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }
        $newLikePostUuid = UUID::random();
        try {
            $likePost = new LikePost(
                $newLikePostUuid,
                $post,
                $user
            );
            $this->likePostsRepository->save($likePost);
        } catch (HttpException | PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            'uuid' => (string)$newLikePostUuid,
        ]);
    }
}