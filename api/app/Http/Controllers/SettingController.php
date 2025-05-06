<?php

namespace App\Http\Controllers;

use App\Http\Requests\Setting\UpdateSettingsRequest;
use App\Http\Resources\Setting\GroupedSettingResource;
use App\Http\Resources\Setting\SettingCollection;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get all settings grouped by their groups
     */
    public function index(Request $request)
    {
        $flat = $request->boolean('flat', false);

        if ($flat) {
            return new SettingCollection(Setting::all());
        }

        // Get grouped settings
        $settings = $this->settings->all();

        // Return structured settings by group
        return response()->json([
            'data' => collect($settings)->map(function ($groupSettings, $groupName) {
                return new GroupedSettingResource($groupSettings);
            }),
        ]);
    }

    /**
     * Update multiple settings
     */
    public function update(UpdateSettingsRequest $request)
    {
        $validated = $request->validated();
        $updated = [];
        $errors = [];

        foreach ($validated as $keySlug => $value) {
            try {
                $setting = Setting::where('key_slug', $keySlug)->first();
                $this->settings->set($setting->key, $value);
                $updated[$setting->key] = $value;
            } catch (\Exception $e) {
                $errors[$keySlug] = $e->getMessage();
            }
        }

        $response = ['updated' => $updated];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $statusCode = empty($updated) ? 422 : 207; // Use 207 Multi-Status when partial success
        } else {
            $statusCode = 200;
        }
        return $this->success($response, $statusCode);
    }
}
