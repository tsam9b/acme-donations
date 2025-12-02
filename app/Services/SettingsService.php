<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SettingsService
{
    /**
     * Get a setting value by key from the database or fallback to settings.json
     *
     * @param string $key The setting key to retrieve
     * @param mixed $default Default value if setting is not found
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Try to get from database first
        $row = DB::table('settings')->first();
        $preferences = $row ? json_decode($row->preferences, true) : [];
        $value = self::array_get_dot($preferences, $key);
        if ($value !== null) {
            return $value;
        }
        // Fallback to settings.json
        $settingsPath = resource_path('js/Pages/Dashboard/Settings/settings.json');
        if (File::exists($settingsPath)) {
            $settingsJson = File::get($settingsPath);
            $settings = json_decode($settingsJson, true);
            $value = self::array_get_dot($settings, $key);
            if ($value !== null) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Get predefined donation amounts
     *
     * @return array
     */
    public static function getPredefinedAmounts(): array
    {
        return self::get('predefinedAmounts', [10, 25, 50, 100]);
    }

    /**
     * Get all settings from database or fallback to settings.json
     *
     * @return array
     */
    public static function getAll(): array
    {
        // Try to get from database first
        $row = DB::table('settings')->first();

        if ($row) {
            return json_decode($row->preferences, true) ?? [];
        }

        // Fallback to settings.json
        $settingsPath = resource_path('js/Pages/Dashboard/Settings/settings.json');

        if (File::exists($settingsPath)) {
            $settingsJson = File::get($settingsPath);
            return json_decode($settingsJson, true) ?? [];
        }

        return [];
    }

    /**
     * Helper to get nested array value using dot notation
     */
    protected static function array_get_dot($array, $key)
    {
        if (!is_array($array)) return null;
        if (array_key_exists($key, $array)) return $array[$key];
        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return null;
            }
            $array = $array[$segment];
        }
        return $array;
    }
}
