<?php

namespace App\Filament\Resources\LawInfoItemResource\Pages;

use App\Filament\Resources\LawInfoItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLawInfoItem extends ViewRecord
{
    protected static string $resource = LawInfoItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
