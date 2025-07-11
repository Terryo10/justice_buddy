<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterRequestResource\Pages;
use App\Models\LetterRequest;
use App\Services\GeminiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class LetterRequestResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Letter Requests';

    protected static ?string $navigationGroup = 'Letter Generation';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Information')
                    ->schema([
                        Forms\Components\TextInput::make('request_id')
                            ->label('Request ID')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('letter_template_id')
                            ->relationship('letterTemplate', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (string $context) => $context === 'edit'),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->disabled(fn (string $context) => $context === 'create'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('client_name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('client_email')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('client_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Client Matters')
                    ->schema([
                        Forms\Components\KeyValue::make('client_matters')
                            ->label('Client Matters')
                            ->keyLabel('Field Name')
                            ->valueLabel('Value')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Generated Content')
                    ->schema([
                        Forms\Components\Textarea::make('generated_letter')
                            ->label('Generated Letter')
                            ->rows(15)
                            ->columnSpanFull()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('document_path')
                            ->label('Document Path')
                            ->disabled()
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled()
                            ->visible(fn (LetterRequest $record) => !empty($record->error_message)),
                    ])
                    ->visible(fn (string $context) => $context !== 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_id')
                    ->label('Request ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Click to copy'),
                
                Tables\Columns\TextColumn::make('letterTemplate.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (LetterRequest $record): ?string {
                        return $record->letterTemplate?->name;
                    }),
                
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('client_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (LetterRequest $record): string => $record->getStatusColor())
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->tooltip(fn (LetterRequest $record): string => $record->created_at->format('F j, Y \a\t g:i A')),
                
                Tables\Columns\TextColumn::make('generated_at')
                    ->label('Generated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->placeholder('Not generated')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('document_path')
                    ->label('Has Document')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\SelectFilter::make('letter_template_id')
                    ->relationship('letterTemplate', 'name')
                    ->label('Template')
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (LetterRequest $record, GeminiService $geminiService) {
                        try {
                            $geminiService->processLetterRequest($record);
                            
                            Notification::make()
                                ->title('Letter regeneration started')
                                ->body('The letter is being regenerated. Please refresh the page in a few moments.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to regenerate letter: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn (LetterRequest $record) => in_array($record->status, ['failed', 'completed'])),
                
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (LetterRequest $record) => route('letter-requests.download', ['requestId' => $record->request_id]))
                    ->openUrlInNewTab()
                    ->visible(fn (LetterRequest $record) => $record->status === 'completed' && !empty($record->document_path)),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('regenerate_selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records, GeminiService $geminiService) {
                            $processed = 0;
                            foreach ($records as $record) {
                                if (in_array($record->status, ['failed', 'completed'])) {
                                    try {
                                        $geminiService->processLetterRequest($record);
                                        $processed++;
                                    } catch (\Exception $e) {
                                        // Log error but continue with other records
                                    }
                                }
                            }
                            
                            Notification::make()
                                ->title('Bulk regeneration started')
                                ->body("Started regenerating {$processed} letters.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListLetterRequests::route('/'),
            'create' => Pages\CreateLetterRequest::route('/create'),
            'view' => Pages\ViewLetterRequest::route('/{record}'),
            'edit' => Pages\EditLetterRequest::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        return $pendingCount > 0 ? 'warning' : 'success';
    }
}