<?php

namespace Tests\Actions\Posts;

use DateTimeImmutable;
use JsonException;
use Nin\ProPhp\Blog\AuthToken;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Exceptions\PostNotFoundException;
use Nin\ProPhp\Blog\Name;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokenNotFoundException;
use Nin\ProPhp\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Http\Actions\Posts\CreatePost;
use Nin\ProPhp\Http\Auth\BearerTokenAuthentication;
use Nin\ProPhp\Http\ErrorResponse;
use Nin\ProPhp\Http\Request;
use Nin\ProPhp\Http\SuccessfulResponse;
use PHPUnit\Framework\TestCase;
use Tests\Dummy\DummyLogger;
use Tests\Dummy\DummyPostRepository;
use Tests\Dummy\DummyTokenRepository;
use Tests\Dummy\DummyUserRepository;

class CreatePostActionTest extends TestCase
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
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function testItReturnsErrorResponseIfNoAuthorizationHeaderProvided(): void
    {
        $request = new Request([], [],
            '{
              "text": "some text",
              "title": "some title"
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
              "text": "some text",
              "title": "some title"
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
     * @throws \Exception
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
              "text": "some text",
              "title": "some title"
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
        $tokensRepository = $this->tokensRepository([$authToken]);
        $authentication = new BearerTokenAuthentication($usersRepository, $tokensRepository);
        $action = new CreatePost($postsRepository, $authentication, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $response->send();
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
     * @return CreatePost
     */
    public function getAction(): CreatePost
    {
        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $tokensRepository = $this->tokensRepository([]);
        $authentication = new BearerTokenAuthentication($usersRepository, $tokensRepository);
        return new CreatePost($postsRepository, $authentication, new DummyLogger());
    }
}