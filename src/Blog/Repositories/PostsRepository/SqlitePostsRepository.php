<?php

namespace Nin\ProPhp\Blog\Repositories\PostsRepository;

use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\UUID;
use PDO;
use PDOStatement;

class SqlitePostsRepository implements IPostsRepository
{
    public function __construct(private PDO $postConnection, private IUsersRepository $usersRepository)
    {
    }

    public function save(Post $post): void
    {
        $statement = $this->postConnection->prepare(
            'INSERT INTO posts (uuid, user_uuid, title, text)
                   VALUES (:uuid, :user_uuid, :title, :text)'
        );
        $statement->execute([
            ':uuid' => (string)$post->uuid(),
            ':user_uuid' => $post->user()->uuid(),
            ':title' => $post->title(),
            ':text' => $post->text(),
        ]);
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

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    private function getPost(PDOStatement $statement, string $uuid): Post
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
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