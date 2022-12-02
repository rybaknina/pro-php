<?php

namespace Nin\ProPhp\Blog\Repositories\CommentsRepository;

use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\Exceptions\CommentNotFoundException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Blog\UUID;
use PDO;
use PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteCommentsRepository implements ICommentsRepository
{
    public function __construct(private PDO                   $connection,
                                private SqlitePostsRepository $sqlitePostsRepository,
                                private SqliteUsersRepository $sqliteUsersRepository,
                                private LoggerInterface       $logger)
    {
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, post_uuid, user_uuid, text)
                   VALUES (:uuid, :post_uuid, :user_uuid, :text)'
        );
        $uuid = (string)$comment->uuid();
        $statement->execute([
            ':uuid' => $uuid,
            ':post_uuid' => $comment->post()->uuid(),
            ':user_uuid' => $comment->user()->uuid(),
            ':text' => $comment->text(),
        ]);
        $this->logger->info("Comment created: $uuid");
    }

    /**
     * @throws InvalidArgumentException
     * @throws CommentNotFoundException
     * @throws UserNotFoundException
     * @throws PostNotFoundException
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
     * @throws UserNotFoundException
     * @throws PostNotFoundException
     */
    private function getComment(PDOStatement $statement, string $uuid): Comment
    {
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (false === $result) {
            $this->logger->warning("Cannot find comment: $uuid");
            throw new CommentNotFoundException(
                "Cannot find comment: $uuid"
            );
        }
        $post = $this->sqlitePostsRepository->get(new UUID($result['post_uuid']));
        $user = $this->sqliteUsersRepository->get(new UUID($result['user_uuid']));
        return new Comment(
            new UUID($result['uuid']),
            $post,
            $user,
            $result['text']
        );
    }
}