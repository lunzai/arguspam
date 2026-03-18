<?php

namespace Lunzai\QuerySearch\Tests\Unit\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lunzai\QuerySearch\Filters\BooleanFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BooleanFilterTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function truthyValues(): array
    {
        return [
            'true' => ['true'],
            '1' => ['1'],
            'on' => ['on'],
            'yes' => ['yes'],
            'TRUE' => ['TRUE'],
            'On' => ['On'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function falsyValues(): array
    {
        return [
            'false' => ['false'],
            '0' => ['0'],
            'off' => ['off'],
            'no' => ['no'],
            'FALSE' => ['FALSE'],
            'Off' => ['Off'],
        ];
    }

    #[DataProvider('truthyValues')]
    public function test_truthy_values_apply_true(string $value): void
    {
        $filter = new BooleanFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())
            ->method('where')
            ->with('two_factor_enabled', true)
            ->willReturnSelf();

        $filter->apply($builder, 'two_factor_enabled', $value);
    }

    #[DataProvider('falsyValues')]
    public function test_falsy_values_apply_false(string $value): void
    {
        $filter = new BooleanFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())
            ->method('where')
            ->with('two_factor_enabled', false)
            ->willReturnSelf();

        $filter->apply($builder, 'two_factor_enabled', $value);
    }

    public function test_unrecognized_value_skips(): void
    {
        $filter = new BooleanFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('where');

        $result = $filter->apply($builder, 'two_factor_enabled', 'maybe');

        $this->assertSame($builder, $result);
    }
}
