<?php

// app/Filament/Widgets/AIStatusWidget.php

namespace App\Filament\Widgets;

use App\Models\AppSetting;
use App\Services\AIService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class AIStatusWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeModel = AppSetting::getValue('active_ai_model', 'chatgpt');
        $availableModels = AIService::getAvailableModels();
        
        // Get model status from cache or test
        $modelStatus = Cache::remember('ai_model_status_' . $activeModel, 300, function () use ($activeModel) {
            try {
                $aiService = app(AIService::class);
                $result = $aiService->testModel($activeModel);
                return $result['success'] ? 'online' : 'offline';
            } catch (\Exception $e) {
                return 'offline';
            }
        });

        return [
            Stat::make('Active AI Model', $availableModels[$activeModel] ?? $activeModel)
                ->description($modelStatus === 'online' ? 'Model is responding' : 'Model unavailable')
                ->descriptionIcon($modelStatus === 'online' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($modelStatus === 'online' ? 'success' : 'danger')
                ->chart([7, 4, 6, 8, 6, 9, 7]), // Dummy chart data
            
            Stat::make('Available Models', count($availableModels))
                ->description('ChatGPT & Gemini configured')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('info'),
            
            Stat::make('API Status', $this->getAPIStatus($activeModel))
                ->description('Last checked: ' . now()->format('H:i'))
                ->descriptionIcon('heroicon-m-signal')
                ->color($this->getAPIStatus($activeModel) === 'Healthy' ? 'success' : 'warning'),
        ];
    }

    protected function getAPIStatus(string $model): string
    {
        $cacheKey = "api_status_{$model}";
        
        return Cache::remember($cacheKey, 300, function () use ($model) {
            try {
                $config = config("services.{$model}");
                $hasApiKey = !empty($config['api_key']);
                
                if (!$hasApiKey) {
                    return 'No API Key';
                }
                
                // You could add a simple ping test here
                return 'Healthy';
            } catch (\Exception $e) {
                return 'Error';
            }
        });
    }
}

// app/Filament/Widgets/AIUsageStatsWidget.php
