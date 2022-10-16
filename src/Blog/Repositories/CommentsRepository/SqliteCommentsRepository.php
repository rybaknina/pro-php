<?php

namespace Nin\ProPhp\Blog\Repositories\CommentsRepository;

use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\Exceptions\CommentNotFoundException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\UUID;
use PDO;
use PDOStatement;

class SqliteCommentsRepository implements ICommentsRepository
{

    public function __construct(private PDO $connection)
    {
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, post_uuid, user_uuid, text)
                   VALUES (:uuid, :post_uuid, :user_uuid, :text)'
        );
        $statement->execute([
            ':uuid' => (string)$comment->uuid(),
            ':post_uuid' => $comment->postUuid(),
            ':user_uuid' => $comment->userUuid(),
            ':text' => $comment->text(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => (string)$uuid,
        ]);
        return $this->getComment($statement, $uuid);
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     */
    private function getComment(PDOStatement $statement, string $uuid): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            throw new CommentNotFoundException(
                "Cannot find comment: $uuid"
            );
        }
        return new Comment(
            new UUID($result['uuid']),
            new UUID($result['post_uuid']),
            new UUID($result['user_uuid']),
            $result['text']
        );
    }
}