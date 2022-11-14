<?php

namespace CommentsRepository;

use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\Exceptions\CommentNotFoundException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentsRepositoryTest extends TestCase
{

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     */
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);
        $usersRepository = new SqliteUsersRepository($connectionStub);
        $postsRepository = new SqlitePostsRepository($connectionStub, $usersRepository);
        $commentsRepository = new SqliteCommentsRepository($connectionStub, $postsRepository, $usersRepository);
        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Cannot find comment: 7be40446-0f83-448c-99fa-7fe6f10dfed4');

        $commentsRepository->get(new UUID('7be40446-0f83-448c-99fa-7fe6f10dfed4'));
    }

    public function testItSavesCommentToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '7be40446-0f83-448c-99fa-7fe6f10dfed4',
                ':post_uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':user_uuid' => '123e4567-e89b-12d3-a456-426614174001',
                ':text' => 'text',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $usersRepository = new SqliteUsersRepository($connectionStub);
        $postsRepository = new SqlitePostsRepository($connectionStub, $usersRepository);
        $commentsRepository = new SqliteCommentsRepository($connectionStub, $postsRepository, $usersRepository);
        $user = new User(
            new UUID('123e4567-e89b-12d3-a456-426614174001'),
            'ivan123',
            new Name('Ivan', 'Nikitin')
        );

        $commentsRepository->save(
            new Comment(
                new UUID('7be40446-0f83-448c-99fa-7fe6f10dfed4'),
                new Post(
                    new UUID('123e4567-e89b-12d3-a456-426614174000'),
                    $user,
                    'title',
                    'text'
                ),
                $user,
                'text'
            )
        );
    }

    /**
     * @throws PostNotFoundException
     * @throws CommentNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function testItGetCommentByUuid(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $usersRepository = new SqliteUsersRepository($connectionStub);
        $statementMockPost = $this->createMock(PDOStatement::class);
        $statementMockPost->method('fetch')->willReturn([
            'uuid' => '7be40446-0f83-448c-99fa-7fe6f10dfed4',
            'post_uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user_uuid' => '2e81188a-30ec-4bdc-aab3-b74d22f59d7c',
            'title' => 'title',
            'text' => 'text',
            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);
        $connectionStub->method('prepare')->willReturn($statementMockPost);
        $postsRepository = new SqlitePostsRepository($connectionStub, $usersRepository);
        $commentsRepository = new SqliteCommentsRepository($connectionStub, $postsRepository, $usersRepository);

        $comment = $commentsRepository->get(new UUID('7be40446-0f83-448c-99fa-7fe6f10dfed4'));

        $this->assertSame('7be40446-0f83-448c-99fa-7fe6f10dfed4', (string)$comment->uuid());
    }
}