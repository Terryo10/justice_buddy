<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterTemplateResource\Pages;
use App\Models\LetterTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LetterTemplateResource extends Resource
{
    protected static ?string $model = LetterTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Letter Templates';

    protected static ?string $navigationGroup = 'Letter Generation';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Letter Template')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Section::make('Template Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                                $context === 'create' ? $set('slug', Str::slug($state)) : null),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(LetterTemplate::class, 'slug', ignoreRecord: true)
                                            ->rules(['alpha_dash']),
                                        
                                        Forms\Components\Select::make('category')
                                            ->options(LetterTemplate::getAvailableCategories())
                                            ->required()
                                            ->searchable(),
                                        
                                        Forms\Components\Textarea::make('description')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Template Content')
                            ->schema([
                                Forms\Components\Section::make('Letter Template')
                                    ->schema([
                                        Forms\Components\Textarea::make('template_content')
                                            ->label('Template Content')
                                            ->required()
                                            ->rows(15)
                                            ->columnSpanFull()
                                            ->hint('Use placeholder variables like {{client_name}}, {{defendant_name}}, etc. These will be replaced with actual values.')
                                            ->hintIcon('heroicon-m-information-circle'),
                                        
                                        Forms\Components\Textarea::make('ai_instructions')
                                            ->label('AI Instructions')
                                            ->rows(4)
                                            ->columnSpanFull()
                                            ->hint('Specific instructions for the AI when generating letters from this template.')
                                            ->placeholder('e.g., Ensure the tone is formal and include specific legal clauses for eviction notices...'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Required Fields')
                            ->schema([
                                Forms\Components\Section::make('Field Configuration')
                                    ->schema([
                                        Forms\Components\TagsInput::make('required_fields')
                                            ->label('Required Fields')
                                            ->required()
                                            ->placeholder('Add required field names...')
                                            ->hint('These fields must be provided by the client when generating a letter.')
                                            ->suggestions([
                                                'client_name',
                                                'client_address',
                                                'client_phone',
                                                'client_email',
                                                'defendant_name', 
                                                'defendant_address',
                                                'case_details',
                                                'incident_date',
                                                'amount_owed',
                                                'contract_date',
                                                'property_address',
                                                'court_date',
                                                'reference_number'
                                            ])
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TagsInput::make('optional_fields')
                                            ->label('Optional Fields')
                                            ->placeholder('Add optional field names...')
                                            ->hint('These fields are optional but can enhance the letter if provided.')
                                            ->suggestions([
                                                'client_id_number',
                                                'lawyer_name',
                                                'lawyer_firm',
                                                'additional_notes',
                                                'witness_names',
                                                'supporting_documents',
                                                'previous_correspondence',
                                                'urgency_level'
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Settings')
                            ->schema([
                                Forms\Components\Section::make('Template Settings')
                                    ->schema([
                                        Forms\Components\TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->label('Sort Order'),
                                        
                                        Forms\Components\Toggle::make('is_active')
                                            ->default(true)
                                            ->label('Active'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => 
                        LetterTemplate::getAvailableCategories()[$state] ?? ucfirst($state)
                    )
                    ->color(fn (string $state): string => match($state) {
                        'eviction' => 'warning',
                        'employment' => 'info',
                        'family' => 'success',
                        'consumer' => 'primary',
                        'criminal' => 'danger',
                        'property' => 'gray',
                        'debt' => 'orange',
                        default => 'secondary'
                    }),
                
                Tables\Columns\TextColumn::make('required_fields')
                    ->label('Required Fields')
                    ->formatStateUsing(function ($state): string {
                        $fields = is_array($state) ? $state : ($state ? json_decode($state, true) ?? [] : []);
                        $count = count($fields);
                        return $count . ' field' . ($count !== 1 ? 's' : '');
                    })
                    ->tooltip(fn (LetterTemplate $record): string => 
                        'Required: ' . implode(', ', $record->required_fields ?? [])
                    ),
                
                Tables\Columns\TextColumn::make('letter_requests_count')
                    ->counts('letterRequests')
                    ->label('Usage Count')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(LetterTemplate::getAvailableCategories()),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Template Preview')
                    ->modalContent(fn (LetterTemplate $record) => view('filament.letter-template-preview', compact('record')))
                    ->modalWidth('4xl'),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
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
            'index' => Pages\ListLetterTemplates::route('/'),
            'create' => Pages\CreateLetterTemplate::route('/create'),
            'view' => Pages\ViewLetterTemplate::route('/{record}'),
            'edit' => Pages\EditLetterTemplate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('is_active', true)->count() > 0 ? 'success' : 'warning';
    }
}