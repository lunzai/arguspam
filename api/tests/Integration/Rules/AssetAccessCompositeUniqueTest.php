<?php

namespace Tests\Unit\Rules;

use App\Rules\AssetAccessCompositeUnique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssetAccessCompositeUniqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test table for asset access
        DB::statement('CREATE TABLE IF NOT EXISTS test_asset_access (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            user_group_id INT,
            asset_id INT,
            role VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )');

        // Insert test data
        DB::table('test_asset_access')->insert([
            ['id' => 1, 'user_id' => 100, 'user_group_id' => null, 'asset_id' => 1, 'role' => 'admin'],
            ['id' => 2, 'user_id' => 101, 'user_group_id' => null, 'asset_id' => 1, 'role' => 'viewer'],
            ['id' => 3, 'user_id' => null, 'user_group_id' => 200, 'asset_id' => 1, 'role' => 'editor'],
            ['id' => 4, 'user_id' => 102, 'user_group_id' => null, 'asset_id' => 2, 'role' => 'admin'],
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up test table
        DB::statement('DROP TABLE IF EXISTS test_asset_access');
        parent::tearDown();
    }

    public function test_rule_can_be_instantiated(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $this->assertInstanceOf(AssetAccessCompositeUnique::class, $rule);
    }

    public function test_rule_implements_validation_rule_interface(): void
    {
        $this->assertTrue(
            in_array('Illuminate\Contracts\Validation\ValidationRule', class_implements(AssetAccessCompositeUnique::class))
        );
    }

    public function test_constructor_sets_properties_correctly(): void
    {
        $rule = new AssetAccessCompositeUnique('test_table', 'user_id', 'admin', '1', '5');

        $reflection = new \ReflectionClass($rule);

        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $this->assertEquals('test_table', $tableProperty->getValue($rule));

        $columnProperty = $reflection->getProperty('column');
        $columnProperty->setAccessible(true);
        $this->assertEquals('user_id', $columnProperty->getValue($rule));

        $roleProperty = $reflection->getProperty('role');
        $roleProperty->setAccessible(true);
        $this->assertEquals('admin', $roleProperty->getValue($rule));

        $assetIdProperty = $reflection->getProperty('assetId');
        $assetIdProperty->setAccessible(true);
        $this->assertEquals('1', $assetIdProperty->getValue($rule));

        $ignoreIdProperty = $reflection->getProperty('ignoreId');
        $ignoreIdProperty->setAccessible(true);
        $this->assertEquals('5', $ignoreIdProperty->getValue($rule));
    }

    public function test_constructor_without_ignore_id(): void
    {
        $rule = new AssetAccessCompositeUnique('test_table', 'user_id', 'admin', '1');

        $reflection = new \ReflectionClass($rule);
        $ignoreIdProperty = $reflection->getProperty('ignoreId');
        $ignoreIdProperty->setAccessible(true);

        $this->assertNull($ignoreIdProperty->getValue($rule));
    }

    public function test_validates_unique_user_role_combination(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // User 100 already has admin role for asset 1, should fail
        $rule->validate('user_id', 100, $fail);
        $this->assertTrue($failCalled);
    }

    public function test_validates_unique_user_group_role_combination(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_group_id', 'editor', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // User group 200 already has editor role for asset 1, should fail
        $rule->validate('user_group_id', 200, $fail);
        $this->assertTrue($failCalled);
    }

    public function test_passes_when_combination_does_not_exist(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // User 103 doesn't have admin role for asset 1, should pass
        $rule->validate('user_id', 103, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_passes_when_different_asset(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '2');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // User 100 has admin role for asset 1, but we're checking asset 2, should pass
        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_passes_when_different_role(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'viewer', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // User 100 has admin role for asset 1, but we're checking viewer role, should pass
        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_ignores_specified_id_when_provided(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // User 100 has admin role for asset 1, but we're ignoring ID 1, should pass
        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_returns_early_when_role_is_empty(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', '', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_returns_early_when_asset_id_is_empty(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_returns_early_when_role_is_null(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', '', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_returns_early_when_asset_id_is_null(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_fail_message_for_user_entity(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $rule->validate('user_id', 100, $fail);

        $this->assertEquals('This user already has admin role assigned to this asset.', $failMessage);
    }

    public function test_fail_message_for_user_group_entity(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_group_id', 'editor', '1');

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $rule->validate('user_group_id', 200, $fail);

        $this->assertEquals('This user group already has editor role assigned to this asset.', $failMessage);
    }

    public function test_fail_message_for_other_column(): void
    {
        // Create a test table with the other column
        DB::statement('CREATE TABLE IF NOT EXISTS test_other_asset_access (
            id INT PRIMARY KEY AUTO_INCREMENT,
            some_other_column INT,
            asset_id INT,
            role VARCHAR(50)
        )');

        DB::table('test_other_asset_access')->insert([
            ['id' => 1, 'some_other_column' => 100, 'asset_id' => 1, 'role' => 'admin'],
        ]);

        $rule = new AssetAccessCompositeUnique('test_other_asset_access', 'some_other_column', 'admin', '1');

        $failMessage = '';
        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $rule->validate('some_other_column', 100, $fail);

        $this->assertEquals('This user group already has admin role assigned to this asset.', $failMessage);

        // Clean up
        DB::statement('DROP TABLE IF EXISTS test_other_asset_access');
    }

    public function test_validates_with_string_values(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // Test with string user ID
        $rule->validate('user_id', '100', $fail);
        $this->assertTrue($failCalled);
    }

    public function test_validates_with_integer_values(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        // Test with integer user ID
        $rule->validate('user_id', 100, $fail);
        $this->assertTrue($failCalled);
    }

    public function test_handles_empty_table(): void
    {
        // Create empty table
        DB::statement('CREATE TABLE IF NOT EXISTS empty_asset_access (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            asset_id INT,
            role VARCHAR(50)
        )');

        $rule = new AssetAccessCompositeUnique('empty_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);

        // Clean up
        DB::statement('DROP TABLE IF EXISTS empty_asset_access');
    }

    public function test_handles_special_characters_in_role(): void
    {
        // Insert test data with special characters in role
        DB::table('test_asset_access')->insert([
            ['id' => 5, 'user_id' => 105, 'user_group_id' => null, 'asset_id' => 1, 'role' => 'admin-special'],
        ]);

        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin-special', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 105, $fail);
        $this->assertTrue($failCalled);
    }

    public function test_handles_large_asset_ids(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '999999');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 100, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_handles_zero_values(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', 0, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_handles_negative_values(): void
    {
        $rule = new AssetAccessCompositeUnique('test_asset_access', 'user_id', 'admin', '1');

        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_id', -1, $fail);
        $this->assertFalse($failCalled);
    }
}
