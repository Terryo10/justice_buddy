<?php


namespace App\Filament\Widgets;

use App\Models\LetterRequest;
use App\Models\AppSetting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AIUsageStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'AI Usage Statistics';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $activeModel = AppSetting::getValue('active_ai_model', 'chatgpt');
        
        // Get usage data for the last 7 days
        $data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            return [
                'date' => $date->format('M j'),
                'requests' => LetterRequest::whereDate('created_at', $date)->count(),
                'completed' => LetterRequest::whereDate('created_at', $date)
                    ->where('status', 'completed')->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Requests',
                    'data' => $data->pluck('requests')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Completed',
                    'data' => $data->pluck('completed')->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}

// app/Filament/Widgets/ModelSwitchWidget.php

namespace App\Filament\Widgets;

use App\Models\AppSetting;
use App\Services\AIService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class ModelSwitchWidget extends Widget
{
    protected static string $view = 'filament.widgets.model-switch-widget';
    protected static ?int $sort = 3;

    public $activeModel;
    public $availableModels;

    public function mount(): void
    {
        $this->activeModel = AppSetting::getValue('active_ai_model', 'chatgpt');
        $this->availableModels = AIService::getAvailableModels();
    }

    public function switchModel(string $model): void
    {
        try {
            if (AIService::switchModel($model)) {
                $this->activeModel = $model;
                
                Notification::make()
                    ->title('AI Model Switched')
                    ->body("Successfully switched to {$this->availableModels[$model]}")
                    ->success()
                    ->send();
                    
                // Refresh the page to update other widgets
                $this->redirect(request()->header('Referer'));
            } else {
                Notification::make()
                    ->title('Error')
                    ->body('Failed to switch AI model')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testModel(string $model): void
    {
        try {
            $aiService = app(AIService::class);
            $result = $aiService->testModel($model);
            
            if ($result['success']) {
                Notification::make()
                    ->title('Model Test Successful')
                    ->body("Model '{$this->availableModels[$model]}' is working correctly")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Model Test Failed')
                    ->body($result['error'])
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Test Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
