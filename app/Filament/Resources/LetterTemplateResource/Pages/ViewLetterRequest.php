<?php

// app/Filament/Resources/LetterTemplateResource/Pages/ViewLetterTemplate.php

namespace App\Filament\Resources\LetterTemplateResource\Pages;

use App\Filament\Resources\LetterTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewLetterTemplate extends ViewRecord
{
    protected static string $resource = LetterTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Template Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        
                        Infolists\Components\TextEntry::make('slug'),
                        
                        Infolists\Components\TextEntry::make('category')
                            ->badge(),
                        
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Template Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('template_content')
                            ->hiddenLabel()
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('AI Instructions')
                    ->schema([
                        Infolists\Components\TextEntry::make('ai_instructions')
                            ->hiddenLabel()
                            ->prose()
                            ->columnSpanFull()
                            ->placeholder('No specific AI instructions provided'),
                    ])
                    ->visible(fn ($record) => !empty($record->ai_instructions)),

                Infolists\Components\Section::make('Field Configuration')
                    ->schema([
                        Infolists\Components\TextEntry::make('required_fields')
                            ->badge()
                            ->separator(',')
                            ->color('danger'),
                        
                        Infolists\Components\TextEntry::make('optional_fields')
                            ->badge()
                            ->separator(',')
                            ->color('info'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Usage Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('letter_requests_count')
                            ->label('Total Requests')
                            ->getStateUsing(fn ($record) => $record->letterRequests()->count()),
                        
                        Infolists\Components\TextEntry::make('completed_requests_count')
                            ->label('Completed')
                            ->getStateUsing(fn ($record) => $record->letterRequests()->where('status', 'completed')->count()),
                        
                        Infolists\Components\TextEntry::make('failed_requests_count')
                            ->label('Failed')
                            ->getStateUsing(fn ($record) => $record->letterRequests()->where('status', 'failed')->count()),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Settings')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active Status'),
                        
                        Infolists\Components\TextEntry::make('sort_order')
                            ->label('Sort Order'),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}