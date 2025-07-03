<?php

namespace App\Filament\Resources\LawInfoItemResource\Pages;

use App\Filament\Resources\LawInfoItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLawInfoItems extends ListRecords
{
    protected static string $resource = LawInfoItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
