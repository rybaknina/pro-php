<?php

namespace Actions\Posts;

use JsonException;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\Posts\FindByUuid;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class FindByUuidActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [], '');
        $postsRepository = $this->postsRepository([]);
        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: uuid"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfPostNotFound(): void
    {
        $request = new Request(['uuid' => 'a6f4d556-7006-47c0-b20d-73bf7c354ab6'], [], '');
        $postsRepository = $this->postsRepository([]);
        $action = new FindByUuid($postsRepository);
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
        $request = new Request(['uuid' => 'a6f4d556-7006-47c0-b20d-73bf7c354ab6'], [], '');
        $postsRepository = $this->postsRepository([
            new Post(
                new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab6'),
                new User(
                    UUID::random(),
                    'ivan',
                    new Name('Ivan', 'Nikitin')
                ),
                'title',
                'text')
        ]);
        $action = new FindByUuid($postsRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"a6f4d556-7006-47c0-b20d-73bf7c354ab6","user":"ivan: Ivan Nikitin","title":"title","text":"text"}}');
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
}