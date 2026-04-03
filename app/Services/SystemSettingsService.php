<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingsService
{
    private const TTL          = 3600;
    private const CACHE_PREFIX = 'settings:';

    /**
     * Retrieve a setting value by key.
     *
     * Falls back to $default if the key does not exist in the database.
     * On Redis/cache outage, falls back to a direct DB read instead of throwing.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember(self::CACHE_PREFIX . $key, self::TTL, function () use ($key, $default) {
                return $this->readFromDb($key, $default);
            });
        } catch (\Throwable) {
            // Cache layer unavailable (Redis outage etc.) — fall back to direct DB
            return $this->readFromDb($key, $default);
        }
    }

    public function set(string $key, mixed $value): void
    {
        SystemSetting::where('key', $key)->update(['value' => (string) $value]);
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function getFloat(string $key, float $default = 0.0): float
    {
        return (float) $this->get($key, $default);
    }

    public function getBool(string $key, bool $default = false): bool
    {
        return (bool) $this->get($key, $default);
    }

    public function getString(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    private function readFromDb(string $key, mixed $default): mixed
    {
        $setting = SystemSetting::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $this->castValue($setting->value, $setting->cast);
    }

    private function castValue(string $value, string $cast): mixed
    {
        return match ($cast) {
            'integer' => (int) $value,
            'float'   => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }
}
