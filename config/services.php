<?php

// config/services.php - Add these to your existing config

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Services Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4'),
        'timeout' => env('OPENAI_TIMEOUT', 60),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2048),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_MODEL', 'gemini-pro'),
        'timeout' => env('GEMINI_TIMEOUT', 60),
        'max_tokens' => env('GEMINI_MAX_TOKENS', 2048),
        'temperature' => env('GEMINI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Letter Generator Configuration
    |--------------------------------------------------------------------------
    */

    'letter_generator' => [
        'generate_pdf' => env('LETTER_GENERATE_PDF', true),
        'max_requests_per_day' => env('LETTER_MAX_REQUESTS_PER_DAY', 10),
        'auto_regenerate_on_update' => env('LETTER_AUTO_REGENERATE', true),
    ],

];