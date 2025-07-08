<?php

namespace App\Filament\Resources\LawyerResource\Pages;

use App\Filament\Resources\LawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewLawyer extends ViewRecord
{
    protected static string $resource = LawyerResource::class;

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
                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('full_name')
                                        ->label('Full Name')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->weight('bold'),
                                    
                                    Infolists\Components\TextEntry::make('firm_name')
                                        ->label('Law Firm'),
                                    
                                    Infolists\Components\TextEntry::make('email')
                                        ->icon('heroicon-m-envelope')
                                        ->copyable(),
                                    
                                    Infolists\Components\TextEntry::make('phone')
                                        ->icon('heroicon-m-phone')
                                        ->copyable(),
                                ]),
                            
                            Infolists\Components\ImageEntry::make('profile_image')
                                ->hiddenLabel()
                                ->grow(false),
                        ])
                        ->from('lg'),
                    ])
                    ->columns(1),

                Infolists\Components\Section::make('Professional Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('license_number')
                            ->label('License Number')
                            ->badge(),
                        
                        Infolists\Components\TextEntry::make('years_experience')
                            ->label('Years of Experience')
                            ->suffix(' years'),
                        
                        Infolists\Components\TextEntry::make('specializations')
                            ->badge()
                            ->separator(','),
                        
                        Infolists\Components\TextEntry::make('languages')
                            ->badge()
                            ->separator(',')
                            ->color('info'),
                        
                        Infolists\Components\TextEntry::make('courts_admitted')
                            ->badge()
                            ->separator(',')
                            ->color('success'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Location & Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('formatted_address')
                            ->label('Address')
                            ->icon('heroicon-m-map-pin'),
                        
                        Infolists\Components\TextEntry::make('website')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab(),
                        
                        Infolists\Components\TextEntry::make('consultation_fee')
                            ->money('ZAR')
                            ->label('Consultation Fee'),
                        
                        Infolists\Components\TextEntry::make('consultation_methods')
                            ->badge()
                            ->separator(','),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Status & Verification')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_verified')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-badge')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        
                        Infolists\Components\TextEntry::make('verified_at')
                            ->dateTime()
                            ->placeholder('Not verified'),
                        
                        Infolists\Components\TextEntry::make('verified_by')
                            ->placeholder('Not verified'),
                        
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active Status'),
                        
                        Infolists\Components\IconEntry::make('accepts_new_clients')
                            ->boolean()
                            ->label('Accepting New Clients'),
                        
                        Infolists\Components\TextEntry::make('rating')
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                $state >= 4.5 => 'success',
                                $state >= 4.0 => 'warning',
                                $state >= 3.0 => 'info',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => number_format($state, 1) . '/5.0'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Biography')
                    ->schema([
                        Infolists\Components\TextEntry::make('bio')
                            ->hiddenLabel()
                            ->prose(),
                    ])
                    ->visible(fn ($record) => !empty($record->bio)),

                Infolists\Components\Section::make('Additional Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('business_hours')
                            ->label('Business Hours')
                            ->listWithLineBreaks()
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    return collect($state)->map(fn ($hours, $day) => "{$day}: {$hours}")->join(', ');
                                }
                                return $state;
                            }),
                        
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Internal Notes')
                            ->prose(),
                    ])
                    ->columns(1)
                    ->visible(fn ($record) => !empty($record->business_hours) || !empty($record->notes)),
            ]);
    }
}