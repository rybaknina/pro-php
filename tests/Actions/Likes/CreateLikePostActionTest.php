<?php

namespace Tests\Actions\Likes;

use DateTimeImmutable;
use JsonException;
use Nin\ProPhp\Blog\AuthToken;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\LikePost;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Nin\ProPhp\Blog\Repositories\LikePostsRepository\ILikePostsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\Likes\CreateLikePost;
use Nin\ProPhp\Http\Auth\BearerTokenAuthentication;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;
use Tests\Dummy\DummyLogger;
use Tests\Dummy\DummyPostRepository;
use Tests\Dummy\DummyTokenRepository;
use Tests\Dummy\DummyUserRepository;

class CreateLikePostActionTest extends TestCase
{
    private DummyUserRepository $dummyUserRepository;
    private DummyPostRepository $dummyPostRepository;
    private DummyTokenRepository $dummyTokenRepository;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->dummyUserRepository = new DummyUserRepository();
        $this->dummyTokenRepository = new DummyTokenRepository();
        $this->dummyPostRepository = new DummyPostRepository();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfWrongUuidFormatProvided(): void
    {
        $token = bin2hex(random_bytes(40));
        $authToken = new AuthToken(
            $token,
            new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab6'),
            // Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );
        $request = new Request([], [
            "HTTP_AUTHORIZATION" => "Bearer " . $token
        ],
            '{
              "post_uuid": "111"
            }'
        );
        $user = new User(
            $authToken->userUuid(),
            'ivan',
            new Name('Ivan', 'Nikitin'),
            'password'
        );
        $usersRepository = $this->usersRepository([$user]);
        $postsRepository = $this->postsRepository([]);
        $likePostsRepository = $this->likePostsRepository([]);
        $tokensRepository = $this->tokensRepository([$authToken]);
        $authentication = new BearerTokenAuthentication($usersRepository, $tokensRepository);
        $action = new CreateLikePost($likePostsRepository, $postsRepository, $authentication, new DummyLogger());

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
    public function testItReturnsErrorResponseIfNoAuthorizationHeaderProvided(): void
    {
        $request = new Request([], [],
            '{
              "post_uuid": null
            }'
        );
        $action = $this->getAction();

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such header in the request: Authorization"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function testItReturnsErrorResponseIfBadToken(): void
    {
        $request = new Request([], [
            "HTTP_AUTHORIZATION" => "Bearer 111"
        ],
            '{
              "post_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab6"
            }'
        );
        $action = $this->getAction();

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Bad token: [111]"}');
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
        $token = bin2hex(random_bytes(40));
        $authToken = new AuthToken(
            $token,
            new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab6'),
            // Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );
        $request = new Request([], [
            "HTTP_AUTHORIZATION" => "Bearer " . $token
        ],
            '{
              "post_uuid": "a6f4d556-7006-47c0-b20d-73bf7c354ab5"
            }'
        );
        $user = new User(
            $authToken->userUuid(),
            'ivan',
            new Name('Ivan', 'Nikitin'),
            'password'
        );
        $post = new Post(
            new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab5'),
            $user,
            'title',
            'text'
        );
        $usersRepository = $this->usersRepository([$user]);
        $postsRepository = $this->postsRepository([$post]);
        $likePostsRepository = $this->likePostsRepository([
            new LikePost(
                new UUID('a6f4d556-7006-47c0-b20d-73bf7c354ab4'),
                $post,
                $user
            )
        ]);
        $tokensRepository = $this->tokensRepository([$authToken]);
        $authentication = new BearerTokenAuthentication($usersRepository, $tokensRepository);
        $action = new CreateLikePost($likePostsRepository, $postsRepository, $authentication, new DummyLogger());

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

    private function tokensRepository(array $tokens): AuthTokensRepositoryInterface
    {
        return $this->dummyTokenRepository->tokensRepository($tokens);
    }

    private function postsRepository(array $posts): IPostsRepository
    {
        return $this->dummyPostRepository->postsRepository($posts);
    }

    private function usersRepository(array $users): IUsersRepository
    {
        return $this->dummyUserRepository->usersRepository($users);
    }

    /**
     * @return CreateLikePost
     */
    public function getAction(): CreateLikePost
    {
        $likePostsRepository = $this->likePostsRepository([]);
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $tokensRepository = $this->tokensRepository([]);
        $authentication = new BearerTokenAuthentication($usersRepository, $tokensRepository);
        return new CreateLikePost($likePostsRepository, $postsRepository, $authentication, new DummyLogger());
    }
}