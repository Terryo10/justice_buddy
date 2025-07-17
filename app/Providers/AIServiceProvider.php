<?php

// app/Providers/AIServiceProvider.php

namespace App\Providers;

use App\Services\AIService;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register individual AI services
        $this->app->singleton(ChatGPTService::class, function ($app) {
            return new ChatGPTService();
        });

        $this->app->singleton(GeminiService::class, function ($app) {
            return new GeminiService();
        });

        // Register the main AI service that handles switching
        $this->app->singleton(AIService::class, function ($app) {
            return new AIService(
                $app->make(ChatGPTService::class),
                $app->make(GeminiService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}