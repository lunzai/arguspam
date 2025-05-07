<?php

namespace App\Http\Controllers;

use App\Http\Resources\Setting\SettingCollection;
use App\Http\Resources\Setting\SettingGroupCollection;
use App\Http\Resources\Setting\SettingGroupResource;
use App\Models\Setting;
use App\Services\SettingsService;

class SettingGroupController extends Controller
{
    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get list of all setting groups
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): SettingGroupCollection
    {
        $groups = $this->settings->groups();

        // Get count of settings in each group
        $groupData = collect($groups)->map(function ($groupName) {
            $count = Setting::where('group', $groupName)->count();

            return [
                'name' => $groupName,
                'settings_count' => $count,
            ];
        });

        return new SettingGroupCollection(
            $groupData->map(function ($item) {
                return new SettingGroupResource($item);
            })
        );
    }

    /**
     * Get all settings within a specific group
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $group): SettingCollection
    {
        // Check if group exists
        if (!in_array($group, $this->settings->groups())) {
            return $this->error('Group not found', 404);
        }

        // Get all settings in the group
        $settings = Setting::where('group', $group)->get();

        return new SettingCollection($settings);
    }
}
