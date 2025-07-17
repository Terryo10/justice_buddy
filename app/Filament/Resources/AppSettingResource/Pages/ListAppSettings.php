<?php

// app/Filament/Resources/AppSettingResource/Pages/ListAppSettings.php

namespace App\Filament\Resources\AppSettingResource\Pages;

use App\Filament\Resources\AppSettingResource;
use App\Models\AppSetting;
use App\Services\AIService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Notifications\Notification;

class ListAppSettings extends ListRecords
{
    protected static string $resource = AppSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('quick_ai_setup')
                ->label('Quick AI Setup')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('ai_model')
                        ->label('Select AI Model')
                        ->options(AIService::getAvailableModels())
                        ->default('chatgpt')
                        ->required(),
                        
                    Forms\Components\TextInput::make('openai_key')
                        ->label('OpenAI API Key')
                        ->password()
                        ->visible(fn (Forms\Get $get) => $get('ai_model') === 'chatgpt')
                        ->helperText('Your OpenAI API key (starts with sk-...)'),
                        
                    Forms\Components\TextInput::make('gemini_key')
                        ->label('Google Gemini API Key')
                        ->password()
                        ->visible(fn (Forms\Get $get) => $get('ai_model') === 'gemini')
                        ->helperText('Your Google AI Studio API key'),
                ])
                ->action(function (array $data) {
                    try {
                        // Set the active AI model
                        AppSetting::setValue('active_ai_model', $data['ai_model'], 'string', 'ai', 'Currently active AI model for the application');
                        
                        // Set API keys in environment (you might want to save these securely)
                        if (isset($data['openai_key']) && !empty($data['openai_key'])) {
                            AppSetting::setValue('openai_api_key', $data['openai_key'], 'string', 'ai', 'OpenAI API Key for ChatGPT');
                        }
                        
                        if (isset($data['gemini_key']) && !empty($data['gemini_key'])) {
                            AppSetting::setValue('gemini_api_key', $data['gemini_key'], 'string', 'ai', 'Google Gemini API Key');
                        }
                        
                        Notification::make()
                            ->title('AI Setup Complete')
                            ->body("Successfully configured {$data['ai_model']} as the active AI model")
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Setup Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Settings')
                ->badge(fn () => AppSetting::count()),
            
            'ai' => Tab::make('AI Configuration')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'ai'))
                ->badge(fn () => AppSetting::where('group', 'ai')->count())
                ->badgeColor('success'),
            
            'api' => Tab::make('API Settings')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'api'))
                ->badge(fn () => AppSetting::where('group', 'api')->count())
                ->badgeColor('info'),
            
            'general' => Tab::make('General')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'general'))
                ->badge(fn () => AppSetting::where('group', 'general')->count())
                ->badgeColor('gray'),
        ];
    }
}
