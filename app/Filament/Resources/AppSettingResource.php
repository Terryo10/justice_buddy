<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages;
use App\Models\AppSetting;
use App\Services\AIService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'App Settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Information')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(AppSetting::class, 'key', ignoreRecord: true)
                            ->disabled(fn (string $context) => $context === 'edit'),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'float' => 'Float',
                                'boolean' => 'Boolean',
                                'array' => 'Array',
                                'json' => 'JSON',
                            ])
                            ->default('string')
                            ->live(),

                        Forms\Components\Select::make('group')
                            ->required()
                            ->options([
                                'general' => 'General',
                                'ai' => 'AI Configuration',
                                'api' => 'API Settings',
                                'mail' => 'Mail Settings',
                                'storage' => 'Storage Settings',
                            ])
                            ->default('general'),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_public')
                            ->label('Public (Available via API)')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Setting Value')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->visible(fn (Forms\Get $get) => !in_array($get('type'), ['boolean', 'array', 'json']))
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('boolean_value')
                            ->label('Value')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'boolean')
                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('value', $state ? '1' : '0'))
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('array_value')
                            ->label('Value (JSON format)')
                            ->rows(4)
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['array', 'json']))
                            ->helperText('Enter valid JSON format')
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                // Validate JSON
                                $decoded = json_decode($state, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $set('value', $state);
                                }
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('value')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'string',
                        'success' => 'boolean',
                        'warning' => 'integer',
                        'info' => 'array',
                        'danger' => 'json',
                        'secondary' => 'float',
                    ]),

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'ai' => 'success',
                        'api' => 'info',
                        'mail' => 'warning',
                        'storage' => 'danger',
                        default => 'primary',
                    }),

                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->label('Public'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'ai' => 'AI Configuration',
                        'api' => 'API Settings',
                        'mail' => 'Mail Settings',
                        'storage' => 'Storage Settings',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'integer' => 'Integer',
                        'float' => 'Float',
                        'boolean' => 'Boolean',
                        'array' => 'Array',
                        'json' => 'JSON',
                    ]),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public Settings'),
            ])
            ->actions([
                Tables\Actions\Action::make('ai_model_switch')
                    ->label('Switch AI Model')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (AppSetting $record) => $record->key === 'active_ai_model')
                    ->form([
                        Forms\Components\Select::make('model')
                            ->label('AI Model')
                            ->options(AIService::getAvailableModels())
                            ->required()
                            ->default(fn (AppSetting $record) => $record->value),
                    ])
                    ->action(function (AppSetting $record, array $data) {
                        if (AIService::switchModel($data['model'])) {
                            $record->update(['value' => $data['model']]);
                            
                            Notification::make()
                                ->title('AI Model Switched')
                                ->body("Successfully switched to {$data['model']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to switch AI model')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('test_ai_model')
                    ->label('Test Model')
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->visible(fn (AppSetting $record) => $record->key === 'active_ai_model')
                    ->action(function (AppSetting $record) {
                        try {
                            $aiService = app(AIService::class);
                            $result = $aiService->testModel($record->value);
                            
                            if ($result['success']) {
                                Notification::make()
                                    ->title('Model Test Successful')
                                    ->body("Model '{$record->value}' is working correctly")
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
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group')
            ->groups([
                Tables\Grouping\Group::make('group')
                    ->label('Group')
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppSettings::route('/'),
            'create' => Pages\CreateAppSetting::route('/create'),
            'edit' => Pages\EditAppSetting::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $aiModel = AppSetting::getValue('active_ai_model', 'chatgpt');
        return strtoupper($aiModel);
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $aiModel = AppSetting::getValue('active_ai_model', 'chatgpt');
        return match ($aiModel) {
            'chatgpt' => 'success',
            'gemini' => 'info',
            default => 'gray',
        };
    }
}