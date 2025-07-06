<?php

namespace Tests\Unit\Enums;

use App\Enums\RequestScope;
use PHPUnit\Framework\TestCase;

class RequestScopeTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = RequestScope::cases();
        $this->assertCount(5, $cases);
        
        $this->assertContains(RequestScope::READ_ONLY, $cases);
        $this->assertContains(RequestScope::READ_WRITE, $cases);
        $this->assertContains(RequestScope::DDL, $cases);
        $this->assertContains(RequestScope::DML, $cases);
        $this->assertContains(RequestScope::ALL, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('ReadOnly', RequestScope::READ_ONLY->value);
        $this->assertEquals('ReadWrite', RequestScope::READ_WRITE->value);
        $this->assertEquals('DDL', RequestScope::DDL->value);
        $this->assertEquals('DML', RequestScope::DML->value);
        $this->assertEquals('All', RequestScope::ALL->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(RequestScope::READ_ONLY, RequestScope::from('ReadOnly'));
        $this->assertEquals(RequestScope::READ_WRITE, RequestScope::from('ReadWrite'));
        $this->assertEquals(RequestScope::DDL, RequestScope::from('DDL'));
        $this->assertEquals(RequestScope::DML, RequestScope::from('DML'));
        $this->assertEquals(RequestScope::ALL, RequestScope::from('All'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(RequestScope::READ_ONLY, RequestScope::tryFrom('ReadOnly'));
        $this->assertEquals(RequestScope::READ_WRITE, RequestScope::tryFrom('ReadWrite'));
        $this->assertEquals(RequestScope::DDL, RequestScope::tryFrom('DDL'));
        $this->assertNull(RequestScope::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        RequestScope::from('invalid');
    }
}