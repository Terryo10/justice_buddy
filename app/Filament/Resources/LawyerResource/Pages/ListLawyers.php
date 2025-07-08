<?php

namespace App\Filament\Resources\LawyerResource\Pages;

use App\Filament\Resources\LawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListLawyers extends ListRecords
{
    protected static string $resource = LawyerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Lawyers')
                ->badge(fn () => \App\Models\Lawyer::count()),
            
            'verified' => Tab::make('Verified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified', true))
                ->badge(fn () => \App\Models\Lawyer::where('is_verified', true)->count())
                ->badgeColor('success'),
            
            'unverified' => Tab::make('Unverified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified', false))
                ->badge(fn () => \App\Models\Lawyer::where('is_verified', false)->count())
                ->badgeColor('warning'),
            
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => \App\Models\Lawyer::where('is_active', true)->count())
                ->badgeColor('info'),
            
            'accepting_clients' => Tab::make('Accepting Clients')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('accepts_new_clients', true))
                ->badge(fn () => \App\Models\Lawyer::where('accepts_new_clients', true)->count())
                ->badgeColor('primary'),
        ];
    }
}

