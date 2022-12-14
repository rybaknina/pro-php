<?php

namespace Nin\ProPhp\Http\Actions\Likes;

use Nin\ProPhp\Blog\Exceptions\AuthException;
use Nin\ProPhp\Blog\Exceptions\HttpException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\ActionInterface;
use Nin\ProPhp\Http\Auth\TokenAuthenticationInterface;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\Response;
use Nin\ProPhp\Http\SuccessfulResponse;
use PDOException;
use Psr\Log\LoggerInterface;

class CreateLikePost implements ActionInterface
{
    public function __construct(
        private ILikePostsRepository         $likePostsRepository,
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
        try {
            $user = $this->authentication->user($request);
            $postUuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException | AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $post = $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $e) {
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
            $this->logger->info("Like on post created: $newLikePostUuid");
        } catch (HttpException | PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }

        return new SuccessfulResponse([
            'uuid' => (string)$newLikePostUuid,
        ]);
    }
}