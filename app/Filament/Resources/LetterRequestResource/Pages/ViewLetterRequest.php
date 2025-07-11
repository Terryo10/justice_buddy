<?php
namespace App\Filament\Resources\LetterRequestResource\Pages;

use App\Filament\Resources\LetterRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewLetterRequest extends ViewRecord
{
    protected static string $resource = LetterRequestResource::class;

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
                Infolists\Components\Section::make('Request Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('request_id')
                            ->label('Request ID')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold')
                            ->copyable(),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->getStatusColor()),
                        
                        Infolists\Components\TextEntry::make('letterTemplate.name')
                            ->label('Template'),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Requested At')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('generated_at')
                            ->label('Generated At')
                            ->dateTime()
                            ->placeholder('Not generated yet'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Client Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('client_name'),
                        
                        Infolists\Components\TextEntry::make('client_email')
                            ->copyable(),
                        
                        Infolists\Components\TextEntry::make('client_phone')
                            ->copyable(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Client Matters')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('client_matters')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Generated Letter')
                    ->schema([
                        Infolists\Components\TextEntry::make('generated_letter')
                            ->hiddenLabel()
                            ->prose()
                            ->columnSpanFull()
                            ->placeholder('Letter not generated yet'),
                    ])
                    ->visible(fn ($record) => !empty($record->generated_letter)),

                Infolists\Components\Section::make('Document')
                    ->schema([
                        Infolists\Components\TextEntry::make('document_path')
                            ->label('Document Path')
                            ->url(fn ($record) => $record->document_path ? asset('storage/' . $record->document_path) : null)
                            ->openUrlInNewTab(),
                    ])
                    ->visible(fn ($record) => !empty($record->document_path)),

                Infolists\Components\Section::make('Error Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_message')
                            ->hiddenLabel()
                            ->prose()
                            ->columnSpanFull()
                            ->color('danger'),
                    ])
                    ->visible(fn ($record) => !empty($record->error_message)),
            ]);
    }
}