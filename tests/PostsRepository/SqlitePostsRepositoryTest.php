<?php

namespace Tests\PostsRepository;

use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Tests\Dummy\DummyLogger;

class SqlitePostsRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $statementStub->method('fetch')->willReturn(false);
        $postsRepository = $this->mockRepository($connectionStub, $statementStub);
        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Cannot find post: 123e4567-e89b-12d3-a456-426614174000');

        $postsRepository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));
    }

    public function testItSavesPostToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':user_uuid' => '123e4567-e89b-12d3-a456-426614174001',
                ':title' => 'title',
                ':text' => 'text',
            ]);
        $postsRepository = $this->mockRepository($connectionStub, $statementMock);

        $postsRepository->save(
            new Post(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                new User(new UUID('123e4567-e89b-12d3-a456-426614174001'),
                    'ivan123',
                    new Name('Ivan', 'Nikitin')),
                'title',
                'text'
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    public function testItGetPostByUuid(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMockPost = $this->createMock(PDOStatement::class);
        $statementMockPost->method('fetch')->willReturn([
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'user_uuid' => '2e81188a-30ec-4bdc-aab3-b74d22f59d7c',
            'title' => 'title',
            'text' => 'text',
            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);
        $postsRepository = $this->mockRepository($connectionStub, $statementMockPost);

        $post = $postsRepository->get(new UUID('123e4567-e89b-12d3-a456-426614174000'));

        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', (string)$post->uuid());
    }

    public function testItDeletesPostFromDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
            ]);
        $postsRepository = $this->mockRepository($connectionStub, $statementMock);

        $postsRepository->delete(
            new UUID('123e4567-e89b-12d3-a456-426614174000')
        );
    }

    /**
     * @param mixed $connectionStub
     * @param mixed $statementMock
     * @return SqlitePostsRepository
     */
    public function mockRepository(mixed $connectionStub, mixed $statementMock): SqlitePostsRepository
    {
        $connectionStub->method('prepare')->willReturn($statementMock);
        $dummyLogger = new DummyLogger();
        $usersRepository = new SqliteUsersRepository($connectionStub, $dummyLogger);
        return new SqlitePostsRepository($connectionStub, $usersRepository, $dummyLogger);
    }
}