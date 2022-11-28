<?php

namespace Nin\ProPhp\Blog\Repositories\LikePostsRepository;

use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Blog\UUID;
use PDO;

class SqliteLikePostsRepository implements ILikePostsRepository
{
    public function __construct(private PDO                   $connection,
                                private SqlitePostsRepository $sqlitePostsRepository,
                                private SqliteUsersRepository $sqliteUsersRepository)
    {
    }

    public function save(LikePost $likePost): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes_post (uuid, post_uuid, user_uuid)
                   VALUES (:uuid, :post_uuid, :user_uuid)'
        );
        $statement->execute([
            ':uuid' => (string)$likePost->uuid(),
            ':post_uuid' => $likePost->post()->uuid(),
            ':user_uuid' => $likePost->user()->uuid()
        ]);
    }

    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByPostUuid(UUID $postUuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes_post WHERE post_uuid = :post_uuid'
        );
        $statement->execute([
            ':post_uuid' => (string)$postUuid,
        ]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $likes = [];
        foreach ($rows as $row) {
            $like = $this->getLike($row);
            $likes[] = $like;
        }
        return $likes;
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException|PostNotFoundException
     */
    private function getLike(array $row): LikePost
    {
        $post = $this->sqlitePostsRepository->get(new UUID($row['post_uuid']));
        $user = $this->sqliteUsersRepository->get(new UUID($row['user_uuid']));
        return new LikePost(
            new UUID($row['uuid']),
            $post,
            $user
        );
    }
}