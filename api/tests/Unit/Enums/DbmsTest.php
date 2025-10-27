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

    public function test_enum_names()
    {
        $this->assertEquals('MYSQL', Dbms::MYSQL->name);
        $this->assertEquals('POSTGRESQL', Dbms::POSTGRESQL->name);
        $this->assertEquals('SQLSERVER', Dbms::SQLSERVER->name);
        $this->assertEquals('ORACLE', Dbms::ORACLE->name);
        $this->assertEquals('MONGODB', Dbms::MONGODB->name);
        $this->assertEquals('REDIS', Dbms::REDIS->name);
        $this->assertEquals('MARIADB', Dbms::MARIADB->name);
    }

    public function test_enum_try_from_all_values()
    {
        $this->assertEquals(Dbms::SQLSERVER, Dbms::tryFrom('sqlserver'));
        $this->assertEquals(Dbms::ORACLE, Dbms::tryFrom('oracle'));
        $this->assertEquals(Dbms::REDIS, Dbms::tryFrom('redis'));
        $this->assertEquals(Dbms::MARIADB, Dbms::tryFrom('mariadb'));
        $this->assertNull(Dbms::tryFrom(''));
    }

    public function test_enum_from_empty_and_null()
    {
        $this->expectException(\ValueError::class);
        Dbms::from('');
    }

    public function test_enum_serialization_and_json()
    {
        $mysql = Dbms::MYSQL;
        $serialized = serialize($mysql);
        $this->assertIsString($serialized);
        $unserialized = unserialize($serialized);
        $this->assertEquals($mysql, $unserialized);

        $this->assertEquals('"mysql"', json_encode(Dbms::MYSQL));
        $this->assertEquals('"postgresql"', json_encode(Dbms::POSTGRESQL));
    }

    public function test_enum_equality_and_switch()
    {
        $mysql1 = Dbms::MYSQL;
        $mysql2 = Dbms::MYSQL;
        $pg = Dbms::POSTGRESQL;

        $this->assertTrue($mysql1 === $mysql2);
        $this->assertFalse($mysql1 === $pg);

        $label = match ($pg) {
            Dbms::MYSQL => 'mysql',
            Dbms::POSTGRESQL => 'postgresql',
            Dbms::SQLSERVER => 'sqlserver',
            Dbms::ORACLE => 'oracle',
            Dbms::MONGODB => 'mongodb',
            Dbms::REDIS => 'redis',
            Dbms::MARIADB => 'mariadb',
        };
        $this->assertEquals('postgresql', $label);
    }

    public function test_enum_array_helpers()
    {
        $cases = Dbms::cases();
        $values = array_column($cases, 'value');
        $names = array_column($cases, 'name');

        $this->assertContains('mysql', $values);
        $this->assertContains('POSTGRESQL', $names);
        $this->assertTrue(in_array(Dbms::MONGODB, $cases));
    }
}
