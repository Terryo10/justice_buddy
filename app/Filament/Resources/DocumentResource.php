<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Str;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $pluralLabel = 'Documents';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Document Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                                $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Document::class, 'slug', ignoreRecord: true),
                        
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000),
                        
                        Select::make('category')
                            ->required()
                            ->options([
                                'Affidavits' => 'Affidavits',
                                'Contracts' => 'Contracts',
                                'Court Forms' => 'Court Forms',
                                'Legal Documents' => 'Legal Documents',
                                'Wills & Estates' => 'Wills & Estates',
                                'Business Documents' => 'Business Documents',
                                'Personal Documents' => 'Personal Documents',
                            ])
                            ->searchable()
                            ->preload(),
                        
                        TagsInput::make('tags')
                            ->separator(',')
                            ->placeholder('Add tags separated by commas'),
                    ])
                    ->columns(2),
                
                Section::make('File Upload')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Document File')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'])
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('documents')
                            ->preserveFilenames()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $file = $state;
                                    $set('file_name', $file->getClientOriginalName());
                                    $set('file_type', $file->getMimeType());
                                    $set('file_extension', $file->getClientOriginalExtension());
                                    $set('file_size', $file->getSize());
                                }
                            }),
                    ]),
                
                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Affidavits' => 'success',
                        'Contracts' => 'info',
                        'Court Forms' => 'warning',
                        'Legal Documents' => 'primary',
                        'Wills & Estates' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('file_extension')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'docx', 'doc' => 'info',
                        'txt' => 'gray',
                        default => 'primary',
                    }),
                
                TextColumn::make('formatted_file_size')
                    ->label('Size')
                    ->sortable(['file_size']),
                
                TextColumn::make('download_count')
                    ->label('Downloads')
                    ->sortable()
                    ->numeric(),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Affidavits' => 'Affidavits',
                        'Contracts' => 'Contracts',
                        'Court Forms' => 'Court Forms',
                        'Legal Documents' => 'Legal Documents',
                        'Wills & Estates' => 'Wills & Estates',
                        'Business Documents' => 'Business Documents',
                        'Personal Documents' => 'Personal Documents',
                    ]),
                
                SelectFilter::make('file_extension')
                    ->label('File Type')
                    ->options([
                        'pdf' => 'PDF',
                        'docx' => 'Word Document',
                        'doc' => 'Word Document (Legacy)',
                        'txt' => 'Text File',
                    ]),
                
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                
                TernaryFilter::make('is_featured')
                    ->label('Featured Status'),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record): string => route('documents.download', $record->id))
                    ->openUrlInNewTab(),
                
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->emptyStateHeading('No documents found')
            ->emptyStateDescription('Upload your first document to get started.');
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
