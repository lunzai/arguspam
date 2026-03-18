<?php

namespace Lunzai\QuerySearch\Tests\Unit\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lunzai\QuerySearch\Filters\LikeFilter;
use PHPUnit\Framework\TestCase;

class LikeFilterTest extends TestCase
{
    public function test_wraps_value_with_percent_wildcards(): void
    {
        $filter = new LikeFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())
            ->method('where')
            ->with('email', 'like', '%john%')
            ->willReturnSelf();

        $filter->apply($builder, 'email', 'john');
    }

    public function test_skips_empty_string(): void
    {
        $filter = new LikeFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('where');

        $result = $filter->apply($builder, 'email', '');

        $this->assertSame($builder, $result);
    }

    public function test_skips_whitespace_only(): void
    {
        $filter = new LikeFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('where');

        $result = $filter->apply($builder, 'email', '   ');

        $this->assertSame($builder, $result);
    }
}
