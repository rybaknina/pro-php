<?php

namespace Actions\Posts;

use JsonException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\Posts\CreatePost;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function testItReturnsErrorResponseIfWrongUuidFormatProvided(): void
    {
        $request = new Request([], [],
            '{
              "author_uuid": "111",
              "text": "some text",
              "title": "some title"
            }'
        );
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 111"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [],
            '{
              "author_uuid": null,
              "text": "some text",
              "title": "some title"
            }'
        );
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Empty field: author_uuid"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([], [],
            '{
              "author_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6",
              "text": "some text",
              "title": "some title"
            }'
        );
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [],
            '{
              "author_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6",
              "text": "some text",
              "title": "some title"
            }'
        );
        $user = new User(
            new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab6'),
            'ivan',
            new Name('Ivan', 'Nikitin')
        );
        $usersRepository = $this->usersRepository([
            $user,
        ]);
        $postsRepository = $this->postsRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
    }

    private function postsRepository(array $posts): IPostsRepository
    {
        return new class($posts) implements IPostsRepository {
            public function __construct(
                private array $posts
            )
            {
            }

            public function save(Post $post): void
            {
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }
            public function delete(UUID $uuid): void
            {
            }
        };
    }

    private function usersRepository(array $users): IUsersRepository
    {
        return new class($users) implements IUsersRepository {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }
        };
    }
}