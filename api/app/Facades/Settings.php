<?php

namespace App\Facades;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Facade;

/**
 * use App\Facades\Settings;

 * // Get a setting with default
 * $minLength = Settings::get('password.min_length', 8);

 * // Check if a setting exists
 * if (Settings::has('theme.primary_color')) {
 *     // Do something
 * }

 * // Update a setting
 * Settings::set('notifications.email_enabled', false);

 * // Get all settings in a group
 * $passwordSettings = Settings::group('password');

 * // Batch update
 * Settings::set([
 *     'password.min_length' => 10,
 *     'theme.primary_color' => '#FF0000',
 * ]);

 * // Create a new setting
 * Settings::create([
 *     'key' => 'app.debug',
 *     'value' => false,
 *     'data_type' => SettingDataType::BOOLEAN,
 *     'group' => 'app',
 *     'label' => 'Debug Mode',
 *     'description' => 'Enable application debugging',
 * ]);
 *
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool has(string $key)
 * @method static bool set(string|array $key, mixed $value = null)
 * @method static \App\Models\Setting create(array $data)
 * @method static array all()
 * @method static array group(string $group)
 * @method static bool renameGroup(string $oldName, string $newName)
 * @method static array groups()
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SettingsService::class;
    }
}
