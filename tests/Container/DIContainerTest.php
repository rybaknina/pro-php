<?php

namespace Tests\Container;

use Nin\ProPhp\Blog\Container\DIContainer;
use Nin\ProPhp\Blog\Exceptions\NotFoundException;
use Nin\ProPhp\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
use Nin\ProPhp\Blog\Repositories\UsersRepository\IUsersRepository;
use PHPUnit\Framework\TestCase;

class DIContainerTest extends TestCase
{
    public function testItThrowsAnExceptionIfCannotResolveType(): void
    {
        $container = $this->getDIContainer();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'Cannot resolve type: Tests\Container\SomeClass'
        );

        $container->get(SomeClass::class);
    }

    /**
     * @throws NotFoundException
     */
    public function testItResolvesClassWithoutDependencies(): void
    {
        $container = $this->getDIContainer();

        $object = $container->get(SomeClassWithoutDependencies::class);

        $this->assertInstanceOf(
            SomeClassWithoutDependencies::class,
            $object
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testItResolvesClassByContract(): void
    {
        $container = $this->getDIContainer();

        $container->bind(
            IUsersRepository::class,
            InMemoryUsersRepository::class
        );

        $object = $container->get(IUsersRepository::class);

        $this->assertInstanceOf(
            InMemoryUsersRepository::class,
            $object
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testItReturnsPredefinedObject(): void
    {
        $container = $this->getDIContainer();

        $container->bind(
            SomeClassWithParameter::class,
            new SomeClassWithParameter(42)
        );

        $object = $container->get(SomeClassWithParameter::class);

        $this->assertInstanceOf(
            SomeClassWithParameter::class,
            $object
        );

        $this->assertSame(42, $object->value());
    }


    /**
     * @throws NotFoundException
     */
    public function testItResolvesClassWithDependencies(): void
    {
        $container = $this->getDIContainer();

        $container->bind(
            SomeClassWithParameter::class,
            new SomeClassWithParameter(10000)
        );

        $object = $container->get(ClassDependingOnAnother::class);

        $this->assertInstanceOf(
            ClassDependingOnAnother::class,
            $object
        );
    }

    private function getDIContainer(): DIContainer
    {
        return new DIContainer();
    }
}