<?php

namespace Tests\Unit\Enums;

use App\Enums\DatabaseScope;
use PHPUnit\Framework\TestCase;

class DatabaseScopeTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = DatabaseScope::cases();
        $this->assertCount(5, $cases);

        $this->assertContains(DatabaseScope::READ_ONLY, $cases);
        $this->assertContains(DatabaseScope::READ_WRITE, $cases);
        $this->assertContains(DatabaseScope::DDL, $cases);
        $this->assertContains(DatabaseScope::ALL, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('ReadOnly', DatabaseScope::READ_ONLY->value);
        $this->assertEquals('ReadWrite', DatabaseScope::READ_WRITE->value);
        $this->assertEquals('DDL', DatabaseScope::DDL->value);
        $this->assertEquals('All', DatabaseScope::ALL->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(DatabaseScope::READ_ONLY, DatabaseScope::from('ReadOnly'));
        $this->assertEquals(DatabaseScope::READ_WRITE, DatabaseScope::from('ReadWrite'));
        $this->assertEquals(DatabaseScope::DDL, DatabaseScope::from('DDL'));
        $this->assertEquals(DatabaseScope::ALL, DatabaseScope::from('All'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(DatabaseScope::READ_ONLY, DatabaseScope::tryFrom('ReadOnly'));
        $this->assertEquals(DatabaseScope::READ_WRITE, DatabaseScope::tryFrom('ReadWrite'));
        $this->assertEquals(DatabaseScope::DDL, DatabaseScope::tryFrom('DDL'));
        $this->assertNull(DatabaseScope::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        DatabaseScope::from('invalid');
    }
}
