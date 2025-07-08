<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LawyerResource\Pages;
use App\Models\Lawyer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class LawyerResource extends Resource
{
    protected static ?string $model = Lawyer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Lawyers';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Lawyer Information')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Personal Info')
                            ->schema([
                                Forms\Components\Section::make('Basic Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('first_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set, Forms\Get $get) {
                                                if ($context === 'create') {
                                                    $lastName = $get('last_name');
                                                    $firmName = $get('firm_name');
                                                    if ($state && $lastName && $firmName) {
                                                        $set('slug', Str::slug($state . ' ' . $lastName . ' ' . $firmName));
                                                    }
                                                }
                                            }),
                                        
                                        Forms\Components\TextInput::make('last_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set, Forms\Get $get) {
                                                if ($context === 'create') {
                                                    $firstName = $get('first_name');
                                                    $firmName = $get('firm_name');
                                                    if ($state && $firstName && $firmName) {
                                                        $set('slug', Str::slug($firstName . ' ' . $state . ' ' . $firmName));
                                                    }
                                                }
                                            }),
                                        
                                        Forms\Components\TextInput::make('firm_name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $context, $state, Forms\Set $set, Forms\Get $get) {
                                                if ($context === 'create') {
                                                    $firstName = $get('first_name');
                                                    $lastName = $get('last_name');
                                                    if ($state && $firstName && $lastName) {
                                                        $set('slug', Str::slug($firstName . ' ' . $lastName . ' ' . $state));
                                                    }
                                                }
                                            }),
                                        
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Lawyer::class, 'slug', ignoreRecord: true)
                                            ->rules(['alpha_dash']),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Contact Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->unique(Lawyer::class, 'email', ignoreRecord: true),
                                        
                                        Forms\Components\TextInput::make('phone')
                                            ->required()
                                            ->tel(),
                                        
                                        Forms\Components\TextInput::make('mobile')
                                            ->tel(),
                                        
                                        Forms\Components\TextInput::make('fax')
                                            ->tel(),
                                        
                                        Forms\Components\TextInput::make('website')
                                            ->url()
                                            ->prefix('https://'),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Profile')
                                    ->schema([
                                        Forms\Components\FileUpload::make('profile_image')
                                            ->image()
                                            ->directory('lawyers')
                                            ->visibility('public')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Textarea::make('bio')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Professional Info')
                            ->schema([
                                Forms\Components\Section::make('Legal Credentials')
                                    ->schema([
                                        Forms\Components\TextInput::make('license_number')
                                            ->required()
                                            ->unique(Lawyer::class, 'license_number', ignoreRecord: true)
                                            ->label('License Number'),
                                        
                                        Forms\Components\TextInput::make('admission_date')
                                            ->label('Admission Date'),
                                        
                                        Forms\Components\TextInput::make('law_society')
                                            ->default('Law Society of South Africa'),
                                        
                                        Forms\Components\TextInput::make('years_experience')
                                            ->numeric()
                                            ->default(0)
                                            ->label('Years of Experience'),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Practice Areas')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('specializations')
                                            ->options(Lawyer::getSpecializationOptions())
                                            ->required()
                                            ->columns(3)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\CheckboxList::make('courts_admitted')
                                            ->options(Lawyer::getCourtsOptions())
                                            ->columns(2)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\CheckboxList::make('languages')
                                            ->options(Lawyer::getLanguageOptions())
                                            ->columns(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Address & Location')
                            ->schema([
                                Forms\Components\Section::make('Address Information')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->required()
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TextInput::make('city')
                                            ->required(),
                                        
                                        Forms\Components\Select::make('province')
                                            ->options(array_combine(Lawyer::getProvinceOptions(), Lawyer::getProvinceOptions()))
                                            ->required()
                                            ->searchable(),
                                        
                                        Forms\Components\TextInput::make('postal_code')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('latitude')
                                            ->numeric()
                                            ->step(0.00000001),
                                        
                                        Forms\Components\TextInput::make('longitude')
                                            ->numeric()
                                            ->step(0.00000001),
                                    ])
                                    ->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Business Details')
                            ->schema([
                                Forms\Components\Section::make('Consultation Information')
                                    ->schema([
                                        Forms\Components\Toggle::make('accepts_new_clients')
                                            ->default(true)
                                            ->label('Accepts New Clients'),
                                        
                                        Forms\Components\TextInput::make('consultation_fee')
                                            ->numeric()
                                            ->prefix('R')
                                            ->step(0.01)
                                            ->label('Consultation Fee'),
                                        
                                        Forms\Components\CheckboxList::make('consultation_methods')
                                            ->options(array_combine(Lawyer::getConsultationMethodOptions(), Lawyer::getConsultationMethodOptions()))
                                            ->columns(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Business Hours')
                                    ->schema([
                                        Forms\Components\KeyValue::make('business_hours')
                                            ->keyLabel('Day')
                                            ->valueLabel('Hours')
                                            ->columnSpanFull(),
                                    ]),
                                
                                Forms\Components\Section::make('Social Media & Online Presence')
                                    ->schema([
                                        Forms\Components\KeyValue::make('social_media')
                                            ->keyLabel('Platform')
                                            ->valueLabel('URL')
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\TagsInput::make('keywords')
                                            ->placeholder('Add SEO keywords')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Status & Verification')
                            ->schema([
                                Forms\Components\Section::make('Status Information')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->default(true)
                                            ->label('Active'),
                                        
                                        Forms\Components\Toggle::make('is_verified')
                                            ->default(false)
                                            ->label('Verified'),
                                        
                                        Forms\Components\DateTimePicker::make('verified_at')
                                            ->label('Verified At'),
                                        
                                        Forms\Components\TextInput::make('verified_by')
                                            ->label('Verified By'),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Rating & Reviews')
                                    ->schema([
                                        Forms\Components\TextInput::make('rating')
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->maxValue(5)
                                            ->default(0),
                                        
                                        Forms\Components\TextInput::make('review_count')
                                            ->numeric()
                                            ->default(0)
                                            ->label('Review Count'),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('Additional Notes')
                                    ->schema([
                                        Forms\Components\Textarea::make('notes')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->label('Internal Notes'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->circular()
                    ->size(50),
                
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->label('First Name'),
                
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable()
                    ->label('Last Name'),
                
                Tables\Columns\TextColumn::make('firm_name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('specializations')
                    ->badge()
                    ->separator(',')
                    ->limit(2)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_array($state) && count($state) > 2) {
                            return implode(', ', $state);
                        }
                        return null;
                    }),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('province')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                
                Tables\Columns\TextColumn::make('years_experience')
                    ->sortable()
                    ->label('Experience')
                    ->suffix(' years'),
                
                Tables\Columns\TextColumn::make('rating')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'warning',
                        $state >= 3.0 => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => number_format($state, 1) . '/5'),
                
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->label('Verified')
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
                Tables\Columns\IconColumn::make('accepts_new_clients')
                    ->boolean()
                    ->label('New Clients')
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
                Tables\Filters\SelectFilter::make('specializations')
                    ->options(array_combine(Lawyer::getSpecializationOptions(), Lawyer::getSpecializationOptions()))
                    ->query(function (Builder $query, array $data): Builder {
                        return $data['value'] 
                            ? $query->whereJsonContains('specializations', $data['value'])
                            : $query;
                    }),
                
                Tables\Filters\SelectFilter::make('province')
                    ->options(array_combine(Lawyer::getProvinceOptions(), Lawyer::getProvinceOptions())),
                
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verification Status')
                    ->boolean()
                    ->trueLabel('Only Verified')
                    ->falseLabel('Only Unverified')
                    ->native(false),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
                    ->native(false),
                
                Tables\Filters\TernaryFilter::make('accepts_new_clients')
                    ->label('Accepting New Clients')
                    ->boolean()
                    ->trueLabel('Only Accepting')
                    ->falseLabel('Not Accepting')
                    ->native(false),
                
                Tables\Filters\Filter::make('rating')
                    ->form([
                        Forms\Components\TextInput::make('rating_from')
                            ->numeric()
                            ->placeholder('Min rating'),
                        Forms\Components\TextInput::make('rating_to')
                            ->numeric()
                            ->placeholder('Max rating'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['rating_from'],
                                fn (Builder $query, $rating): Builder => $query->where('rating', '>=', $rating),
                            )
                            ->when(
                                $data['rating_to'],
                                fn (Builder $query, $rating): Builder => $query->where('rating', '<=', $rating),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (Lawyer $record): void {
                        $record->update([
                            'is_verified' => true,
                            'verified_at' => now(),
                            'verified_by' => Auth::user()->name ?? 'System',
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Lawyer $record): bool => !$record->is_verified),
                
                Tables\Actions\Action::make('unverify')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (Lawyer $record): void {
                        $record->update([
                            'is_verified' => false,
                            'verified_at' => null,
                            'verified_by' => null,
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Lawyer $record): bool => $record->is_verified),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (Lawyer $record): void {
                                $record->update([
                                    'is_verified' => true,
                                    'verified_at' => now(),
                                    'verified_by' => Auth::user()->name ?? 'System',
                                ]);
                            });
                        })
                        ->requiresConfirmation(),
                    
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
            'index' => Pages\ListLawyers::route('/'),
            'create' => Pages\CreateLawyer::route('/create'),
            'view' => Pages\ViewLawyer::route('/{record}'),
            'edit' => Pages\EditLawyer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }
}