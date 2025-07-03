<?php

namespace Tests\Unit\Services;

use App\Enums\SettingDataType;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingsService $settingsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settingsService = new SettingsService();
        
        // Clear cache before each test
        Cache::flush();
    }

    public function test_get_returns_setting_value_when_exists(): void
    {
        $setting = Setting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
            'data_type' => SettingDataType::STRING
        ]);

        $result = $this->settingsService->get('test_key');

        $this->assertEquals('test_value', $result);
    }

    public function test_get_returns_default_when_setting_not_exists(): void
    {
        $result = $this->settingsService->get('non-existent-key', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function test_get_returns_null_when_setting_not_exists_and_no_default(): void
    {
        $result = $this->settingsService->get('non-existent-key');

        $this->assertNull($result);
    }

    public function test_has_returns_true_when_setting_exists(): void
    {
        Setting::factory()->create([
            'key' => 'test_key'
        ]);

        $result = $this->settingsService->has('test_key');

        $this->assertTrue($result);
    }

    public function test_has_returns_false_when_setting_not_exists(): void
    {
        $result = $this->settingsService->has('non-existent-key');

        $this->assertFalse($result);
    }

    public function test_set_updates_existing_setting(): void
    {
        $setting = Setting::factory()->create([
            'key' => 'test_key',
            'value' => 'old_value',
            'data_type' => SettingDataType::STRING
        ]);

        $result = $this->settingsService->set('test_key', 'new_value');

        $this->assertTrue($result);
        $this->assertEquals('new_value', $setting->fresh()->typed_value);
    }

    public function test_set_throws_exception_when_setting_not_exists(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Setting with key 'non-existent-key' not found");

        $this->settingsService->set('non-existent-key', 'value');
    }

    public function test_set_with_array_calls_set_many(): void
    {
        $setting1 = Setting::factory()->create([
            'key' => 'key1',
            'value' => 'old_value1'
        ]);
        
        $setting2 = Setting::factory()->create([
            'key' => 'key2',
            'value' => 'old_value2'
        ]);

        $result = $this->settingsService->set([
            'key1' => 'new_value1',
            'key2' => 'new_value2'
        ]);

        $this->assertTrue($result);
        $this->assertEquals('new_value1', $setting1->fresh()->typed_value);
        $this->assertEquals('new_value2', $setting2->fresh()->typed_value);
    }

    public function test_create_creates_new_setting_with_valid_data(): void
    {
        $data = [
            'key' => 'new_key',
            'value' => 'new_value',
            'data_type' => SettingDataType::STRING,
            'group' => 'test_group',
            'label' => 'Test Label',
            'description' => 'Test Description'
        ];

        $setting = $this->settingsService->create($data);

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertEquals('new_key', $setting->key);
        $this->assertEquals('new_value', $setting->value);
        $this->assertEquals(SettingDataType::STRING, $setting->data_type);
        $this->assertEquals('test_group', $setting->group);
        $this->assertEquals('Test Label', $setting->label);
        $this->assertEquals('Test Description', $setting->description);
    }

    public function test_create_throws_exception_for_invalid_data_type(): void
    {
        $data = [
            'key' => 'new_key',
            'value' => 'not_a_number',
            'data_type' => SettingDataType::INTEGER
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for type integer');

        $this->settingsService->create($data);
    }

    public function test_all_returns_settings_grouped_by_group(): void
    {
        Setting::factory()->create([
            'key' => 'key1',
            'value' => 'value1',
            'group' => 'group1'
        ]);
        
        Setting::factory()->create([
            'key' => 'key2',
            'value' => 'value2',
            'group' => 'group1'
        ]);
        
        Setting::factory()->create([
            'key' => 'key3',
            'value' => 'value3',
            'group' => 'group2'
        ]);

        $result = $this->settingsService->all();

        $this->assertArrayHasKey('group1', $result);
        $this->assertArrayHasKey('group2', $result);
        $this->assertArrayHasKey('key1', $result['group1']);
        $this->assertArrayHasKey('key2', $result['group1']);
        $this->assertArrayHasKey('key3', $result['group2']);
        $this->assertEquals('value1', $result['group1']['key1']);
        $this->assertEquals('value2', $result['group1']['key2']);
        $this->assertEquals('value3', $result['group2']['key3']);
    }

    public function test_group_returns_settings_for_specific_group(): void
    {
        Setting::factory()->create([
            'key' => 'key1',
            'value' => 'value1',
            'group' => 'target_group'
        ]);
        
        Setting::factory()->create([
            'key' => 'key2',
            'value' => 'value2',
            'group' => 'other_group'
        ]);

        $result = $this->settingsService->group('target_group');

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayNotHasKey('key2', $result);
    }

    public function test_rename_group_updates_all_settings_in_group(): void
    {
        $setting1 = Setting::factory()->create([
            'key' => 'key1',
            'group' => 'old_group'
        ]);
        
        $setting2 = Setting::factory()->create([
            'key' => 'key2',
            'group' => 'old_group'
        ]);
        
        $setting3 = Setting::factory()->create([
            'key' => 'key3',
            'group' => 'other_group'
        ]);

        $result = $this->settingsService->renameGroup('old_group', 'new_group');

        $this->assertTrue($result);
        $this->assertEquals('new_group', $setting1->fresh()->group);
        $this->assertEquals('new_group', $setting2->fresh()->group);
        $this->assertEquals('other_group', $setting3->fresh()->group);
    }

    public function test_groups_returns_all_distinct_groups(): void
    {
        Setting::factory()->create(['group' => 'group1']);
        Setting::factory()->create(['group' => 'group2']);
        Setting::factory()->create(['group' => 'group1']);
        Setting::factory()->create(['group' => null]);

        $result = $this->settingsService->groups();

        $this->assertCount(2, $result);
        $this->assertContains('group1', $result);
        $this->assertContains('group2', $result);
        $this->assertNotContains(null, $result);
    }

    public function test_caching_works_for_get_method(): void
    {
        $setting = Setting::factory()->create([
            'key' => 'cached_key',
            'value' => 'cached_value'
        ]);

        // First call should hit database
        $result1 = $this->settingsService->get('cached_key');
        
        // Update the setting directly in database
        $setting->update(['value' => 'updated_value']);
        
        // Second call should return cached value
        $result2 = $this->settingsService->get('cached_key');

        $this->assertEquals('cached_value', $result1);
        $this->assertEquals('cached_value', $result2); // Should be cached
    }

    public function test_cache_invalidation_works_after_set(): void
    {
        $setting = Setting::factory()->create([
            'key' => 'test_key',
            'value' => 'old_value'
        ]);

        // Get the value to cache it
        $this->settingsService->get('test_key');
        
        // Update the setting
        $this->settingsService->set('test_key', 'new_value');
        
        // Get the value again - should be fresh from database
        $result = $this->settingsService->get('test_key');

        $this->assertEquals('new_value', $result);
    }
}