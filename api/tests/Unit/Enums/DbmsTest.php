<?php

namespace Tests\Unit\Enums;

use App\Enums\Dbms;
use PHPUnit\Framework\TestCase;

class DbmsTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = Dbms::cases();
        $this->assertCount(7, $cases);
        
        $this->assertContains(Dbms::MYSQL, $cases);
        $this->assertContains(Dbms::POSTGRESQL, $cases);
        $this->assertContains(Dbms::SQLSERVER, $cases);
        $this->assertContains(Dbms::ORACLE, $cases);
        $this->assertContains(Dbms::MONGODB, $cases);
        $this->assertContains(Dbms::REDIS, $cases);
        $this->assertContains(Dbms::MARIADB, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('mysql', Dbms::MYSQL->value);
        $this->assertEquals('postgresql', Dbms::POSTGRESQL->value);
        $this->assertEquals('sqlserver', Dbms::SQLSERVER->value);
        $this->assertEquals('oracle', Dbms::ORACLE->value);
        $this->assertEquals('mongodb', Dbms::MONGODB->value);
        $this->assertEquals('redis', Dbms::REDIS->value);
        $this->assertEquals('mariadb', Dbms::MARIADB->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(Dbms::MYSQL, Dbms::from('mysql'));
        $this->assertEquals(Dbms::POSTGRESQL, Dbms::from('postgresql'));
        $this->assertEquals(Dbms::SQLSERVER, Dbms::from('sqlserver'));
        $this->assertEquals(Dbms::ORACLE, Dbms::from('oracle'));
        $this->assertEquals(Dbms::MONGODB, Dbms::from('mongodb'));
        $this->assertEquals(Dbms::REDIS, Dbms::from('redis'));
        $this->assertEquals(Dbms::MARIADB, Dbms::from('mariadb'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(Dbms::MYSQL, Dbms::tryFrom('mysql'));
        $this->assertEquals(Dbms::POSTGRESQL, Dbms::tryFrom('postgresql'));
        $this->assertEquals(Dbms::MONGODB, Dbms::tryFrom('mongodb'));
        $this->assertNull(Dbms::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        Dbms::from('invalid');
    }
}