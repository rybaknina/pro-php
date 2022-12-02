<?php

namespace Tests\Commands;

use Nin\ProPhp\Blog\Commands\Arguments;
use Nin\ProPhp\Blog\Exceptions\ArgumentsException;
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase
{
    public function testItReturnsArgumentsValueByName(): void
    {
        // Подготовка
        $arguments = new Arguments(['some_key' => 'some_value']);

        // Действие
        $value = $arguments->get('some_key');

        // Проверка
        $this->assertEquals('some_value', $value);
    }

    public function testItReturnsValuesAsStrings(): void
    {
        // Создаём объект с числом в качестве значения аргумента
        $arguments = new Arguments(['some_key' => 123]);

        $value = $arguments->get('some_key');

        $this->assertSame('123', $value);
        // Можно также явно проверить,
        // что значение является строкой
        $this->assertIsString($value);
    }

    public function testItThrowsAnExceptionWhenArgumentIsAbsent(): void
    {
        // Подготавливаем объект с пустым набором данных
        $arguments = new Arguments([]);
        // Описываем тип ожидаемого исключения
        $this->expectException(ArgumentsException::class);

        // и его сообщение
        $this->expectExceptionMessage("No such argument: some_key");

        // Выполняем действие, приводящее к выбрасыванию исключения
        $arguments->get('some_key');
    }
    // Связываем тест с провайдером данных с помощью аннотации @dataProvider
    // У теста два агрумента
    // В одном тестовом наборе из провайдера данных два значения
    /**
     * @dataProvider argumentsProvider
     * @throws ArgumentsException
     */
    public function testItConvertsArgumentsToStrings(
        $inputValue,
        $expectedValue
    ): void
    {
        // Подставляем первое значение из тестового набора
        $arguments = new Arguments(['some_key' => $inputValue]);

        $value = $arguments->get('some_key');

        // Сверяем со вторым значением из тестового набора
        $this->assertEquals($expectedValue, $value);
    }

    // Провайдер данных
    private function argumentsProvider(): iterable
    {
        return [
            ['some_string', 'some_string'],
            [' some_string', 'some_string'],
            [' some_string ', 'some_string'],
            [123, '123'],
            [12.3, '12.3'],
        ];
    }
}