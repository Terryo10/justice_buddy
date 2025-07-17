<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'array', 'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value, string $type = 'string', string $group = 'general', string $description = ''): void
    {
        $processedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'array', 'json' => json_encode($value),
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $processedValue,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings for a group
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(function ($setting) {
                $value = match ($setting->type) {
                    'boolean' => (bool) $setting->value,
                    'integer' => (int) $setting->value,
                    'float' => (float) $setting->value,
                    'array', 'json' => json_decode($setting->value, true),
                    default => $setting->value,
                };
                
                return [$setting->key => $value];
            })
            ->toArray();
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete a setting
     */
    public static function forget(string $key): bool
    {
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * Get all public settings (for API)
     */
    public static function getPublicSettings(): array
    {
        return static::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($setting) {
                $value = match ($setting->type) {
                    'boolean' => (bool) $setting->value,
                    'integer' => (int) $setting->value,
                    'float' => (float) $setting->value,
                    'array', 'json' => json_decode($setting->value, true),
                    default => $setting->value,
                };
                
                return [$setting->key => $value];
            })
            ->toArray();
    }
}