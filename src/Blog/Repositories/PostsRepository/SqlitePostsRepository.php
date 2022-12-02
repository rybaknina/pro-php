<?php

namespace Nin\ProPhp\Blog\Repositories\PostsRepository;

use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements IPostsRepository
{
    public function __construct(
        private PDO              $postConnection,
        private IUsersRepository $usersRepository,
        private LoggerInterface  $logger
    )
    {
    }

    public function save(Post $post): void
    {
        $statement = $this->postConnection->prepare(
            'INSERT INTO posts (uuid, user_uuid, title, text)
                   VALUES (:uuid, :user_uuid, :title, :text)'
        );
        $uuid = (string)$post->uuid();
        $statement->execute([
            ':uuid' => $uuid,
            ':user_uuid' => $post->user()->uuid(),
            ':title' => $post->title(),
            ':text' => $post->text(),
        ]);
        $this->logger->info("Post created: $uuid");
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->postConnection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        return $this->getPost($statement, $uuid);
    }

    public function delete(UUID $uuid): void
    {
        $statement = $this->postConnection->prepare(
            'DELETE FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
    }


    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    private function getPost(PDOStatement $statement, string $uuid): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            $this->logger->warning("Cannot find post: $uuid");
            throw new PostNotFoundException(
                "Cannot find post: $uuid"
            );
        }

        $user = $this->usersRepository->get(new UUID($result['user_uuid']));
        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text']
        );
    }
}