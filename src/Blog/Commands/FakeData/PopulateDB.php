<?php

namespace Nin\ProPhp\Blog\Commands\FakeData;

use Nin\ProPhp\Blog\Comment;
use Nin\ProPhp\Blog\Exceptions\InvalidArgumentException;
use Nin\ProPhp\Blog\Post;
use Nin\ProPhp\Blog\Repositories\CommentsRepository\ICommentsRepository;
use Nin\ProPhp\Blog\Repositories\PostsRepository\IPostsRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use Nin\ProPhp\Blog\User;
use Nin\ProPhp\Blog\UUID;
use Nin\ProPhp\Blog\Name;
use Symfony\Component\Console\Command\Command;
use Faker\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDB extends Command
{
    // Внедряем генератор тестовых данных и
    // репозитории пользователей и статей
    public function __construct(
        private Generator           $faker,
        private IUsersRepository    $usersRepository,
        private IPostsRepository    $postsRepository,
        private ICommentsRepository $commentsRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('fake-data:populate-db')
            ->setDescription('Populates DB with fake data')
            ->addOption(
                'users-number',
                'u',
                InputOption::VALUE_OPTIONAL,
                'количество создаваемых пользователей ',
            )
            ->addOption(
                'posts-number',
                'p',
                InputOption::VALUE_OPTIONAL,
                'количество создаваемых статей',
            )
            ->addOption(
                'comments-number',
                'c',
                InputOption::VALUE_OPTIONAL,
                'количество создаваемых комментариев',
            );

    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {

        $usersNumber = $input->getOption('users-number') ?: 10;
        $postsNumber = $input->getOption('posts-number') ?: 20;
        $commentsNumber = $input->getOption('comments-number') ?: 2;

        // Создаём десять пользователей
        $users = [];
        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            $output->writeln('User created: ' . $user->username());
        }

        $posts = [];
        // От имени каждого пользователя
        // создаём по двадцать статей
        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                $output->writeln('Post created: ' . $post->title());
            }
        }

        foreach ($posts as $post) {
            for ($i = 0; $i < $commentsNumber; $i++) {
                $comment = $this->createFakeComment($post);
                $output->writeln('Comment created: ' . $comment->text());
            }
        }
        return Command::SUCCESS;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createFakeUser(): User
    {
        $user = User::createFrom(
        // Генерируем имя пользователя
            $this->faker->userName,
            // Генерируем пароль
            $this->faker->password,
            new Name(
            // Генерируем имя
                $this->faker->firstName,
                // Генерируем фамилию
                $this->faker->lastName
            )
        );

        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);

        return $user;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            // Генерируем предложение не длиннее шести слов
            $this->faker->sentence(6, true),
            // Генерируем текст
            $this->faker->realText
        );

        // Сохраняем статью в репозиторий
        $this->postsRepository->save($post);

        return $post;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createFakeComment(Post $post): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $post,
            $post->user(),
            $this->faker->realText
        );
        $this->commentsRepository->save($comment);

        return $comment;
    }
}