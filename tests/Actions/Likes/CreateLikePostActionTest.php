<?php

namespace Tests\Actions\Likes;

use JsonException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Exceptions\UserNotFoundException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\Likes\CreateLikePost;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class CreateLikePostActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfWrongUuidFormatProvided(): void
    {
        $request = new Request([], [],
            '{
              "post_uuid": "111",
              "author_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6"
            }'
        );
        $likePostsRepository = $this->likePostsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $action = new CreateLikePost($likePostsRepository, $postsRepository, $usersRepository);

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
              "author_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6",
              "post_uuid": null
            }'
        );
        $likePostsRepository = $this->likePostsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $action = new CreateLikePost($likePostsRepository, $postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Empty field: post_uuid"}');
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
              "post_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6",
              "author_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6"
            }'
        );
        $likePostsRepository = $this->likePostsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $action = new CreateLikePost($likePostsRepository, $postsRepository, $usersRepository);

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
              "post_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab5"
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
        $post = new Post(
            new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab5'),
            $user,
            'title',
            'text'
        );
        $postsRepository = $this->postsRepository([
            $post,
        ]);
        $likePostsRepository = $this->likePostsRepository([
            new LikePost(
                new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab4'),
                $post,
                $user
            )
        ]);
        $action = new CreateLikePost($likePostsRepository, $postsRepository, $usersRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
    }

    private function likePostsRepository(array $likePosts): ILikePostsRepository
    {
        return new class($likePosts) implements ILikePostsRepository {
            public function __construct(
                private array $likePosts
            )
            {
            }

            public function save(LikePost $likePost): void
            {
            }

            public function getByPostUuid(UUID $postUuid): array
            {
                return $this->likePosts;
            }
        };
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
                foreach ($this->posts as $post) {
                    if ($post instanceof Post && $uuid == $post->uuid()) {
                        return $post;
                    }
                }
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