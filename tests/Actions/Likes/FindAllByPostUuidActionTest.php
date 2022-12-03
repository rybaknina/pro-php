<?php

namespace Tests\Actions\Likes;

use JsonException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\Likes\FindAllByPostUuid;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;

class FindAllByPostUuidActionTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoUuidProvided(): void
    {
        $request = new Request([], [], '');
        $likePostsRepository = $this->likePostsRepository([]);
        $action = new FindAllByPostUuid($likePostsRepository);
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
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request(['uuid' => 'a6f4d556-7006-47c0-b20d-73bf7c354ab6'], [], '');
        $user = new User(
            new UUID('db13f0e7-f30a-4f6c-898d-bfe5428734dd'),
            'ivan',
            new Name('Ivan', 'Nikitin'),
            'password'
        );
        $likePostsRepository = $this->likePostsRepository([
            new LikePost(
                new UUID('a1c40690-7619-44a0-a433-215292af0b41'),
                new Post(
                    new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab6'),
                    $user,
                    'title',
                    'text'
                ),
                $user
            )
        ]);
        $action = new FindAllByPostUuid($likePostsRepository);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":[{"uuid":"a1c40690-7619-44a0-a433-215292af0b41","post_uuid":"a6f4d556-7006-47c0-b20d-73bf7c354ab6","user_uuid":"db13f0e7-f30a-4f6c-898d-bfe5428734dd"}]}');
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
}