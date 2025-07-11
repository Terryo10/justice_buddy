<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatRuleResource\Pages;
use App\Filament\Resources\ChatRuleResource\RelationManagers;
use App\Models\ChatRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChatRuleResource extends Resource
{
    protected static ?string $model = ChatRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Chat Rules';

    protected static ?string $navigationGroup = 'AI Chat';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rule Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'instruction' => 'Instruction',
                                'constraint' => 'Constraint',
                                'context' => 'Context',
                                'guideline' => 'Guideline',
                            ])
                            ->default('instruction')
                            ->native(false),

                        Forms\Components\TextInput::make('priority')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher numbers = higher priority'),

                        Forms\Components\Textarea::make('rule_text')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull()
                            ->helperText('The actual rule or instruction for the AI'),

                        Forms\Components\TextInput::make('model_name')
                            ->maxLength(100)
                            ->helperText('Leave empty to apply to all models, or specify a model name')
                            ->placeholder('e.g., gemini-pro'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'instruction',
                        'warning' => 'constraint',
                        'info' => 'context',
                        'success' => 'guideline',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('rule_text')
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('model_name')
                    ->placeholder('All models')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'instruction' => 'Instruction',
                        'constraint' => 'Constraint',
                        'context' => 'Context',
                        'guideline' => 'Guideline',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
                    ->native(false),

                Tables\Filters\Filter::make('has_model')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('model_name'))
                    ->label('Model Specific'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
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
            'index' => Pages\ListChatRules::route('/'),
            'create' => Pages\CreateChatRule::route('/create'),
            'edit' => Pages\EditChatRule::route('/{record}/edit'),
        ];
    }
}
