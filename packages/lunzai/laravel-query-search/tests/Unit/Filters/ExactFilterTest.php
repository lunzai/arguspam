<?php

namespace Lunzai\QuerySearch\Tests\Unit\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Lunzai\QuerySearch\Filters\ExactFilter;
use PHPUnit\Framework\TestCase;

class ExactFilterTest extends TestCase
{
    private function makeBuilder(): Builder
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $builder = $this->getMockBuilder(Builder::class)
            ->setConstructorArgs([$queryBuilder])
            ->onlyMethods(['where'])
            ->getMock();

        return $builder;
    }

    public function test_applies_exact_where_clause(): void
    {
        $filter = new ExactFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())
            ->method('where')
            ->with('status', 'active')
            ->willReturnSelf();

        $result = $filter->apply($builder, 'status', 'active');

        $this->assertSame($builder, $result);
    }

    public function test_skips_empty_string(): void
    {
        $filter = new ExactFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('where');

        $result = $filter->apply($builder, 'status', '');

        $this->assertSame($builder, $result);
    }

    public function test_skips_whitespace_only_string(): void
    {
        $filter = new ExactFilter;

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('where');

        $result = $filter->apply($builder, 'status', '   ');

        $this->assertSame($builder, $result);
    }
}
