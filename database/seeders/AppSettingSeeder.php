<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // AI Configuration
            [
                'key' => 'active_ai_model',
                'value' => 'chatgpt',
                'type' => 'string',
                'group' => 'ai',
                'description' => 'Currently active AI model for the application (chatgpt or gemini)',
                'is_public' => true,
            ],
            [
                'key' => 'ai_model_timeout',
                'value' => '60',
                'type' => 'integer',
                'group' => 'ai',
                'description' => 'Timeout in seconds for AI API requests',
                'is_public' => false,
            ],
            [
                'key' => 'ai_max_tokens',
                'value' => '2048',
                'type' => 'integer',
                'group' => 'ai',
                'description' => 'Maximum tokens for AI responses',
                'is_public' => false,
            ],
            [
                'key' => 'ai_temperature',
                'value' => '0.7',
                'type' => 'float',
                'group' => 'ai',
                'description' => 'Temperature setting for AI responses (0.0 to 1.0)',
                'is_public' => false,
            ],
            
            // API Settings
            [
                'key' => 'api_rate_limit',
                'value' => '100',
                'type' => 'integer',
                'group' => 'api',
                'description' => 'API rate limit per hour',
                'is_public' => true,
            ],
            [
                'key' => 'api_pagination_limit',
                'value' => '50',
                'type' => 'integer',
                'group' => 'api',
                'description' => 'Maximum items per API page',
                'is_public' => true,
            ],
            [
                'key' => 'api_cache_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'api',
                'description' => 'Enable API response caching',
                'is_public' => false,
            ],
            
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'Justice Buddy',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name',
                'is_public' => true,
            ],
            [
                'key' => 'app_version',
                'value' => '1.0.0',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Current application version',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable maintenance mode',
                'is_public' => true,
            ],
            [
                'key' => 'supported_languages',
                'value' => '["en", "af", "zu", "xh"]',
                'type' => 'array',
                'group' => 'general',
                'description' => 'Supported application languages',
                'is_public' => true,
            ],
            
            // Letter Generation Settings
            [
                'key' => 'letter_auto_generate_pdf',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Automatically generate PDF for letters',
                'is_public' => false,
            ],
            [
                'key' => 'letter_max_requests_per_day',
                'value' => '10',
                'type' => 'integer',
                'group' => 'general',
                'description' => 'Maximum letter requests per device per day',
                'is_public' => true,
            ],
            
            // Feature Flags
            [
                'key' => 'feature_chat_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable AI chat feature',
                'is_public' => true,
            ],
            [
                'key' => 'feature_letter_generation_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable letter generation feature',
                'is_public' => true,
            ],
            [
                'key' => 'feature_lawyer_directory_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable lawyer directory feature',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}