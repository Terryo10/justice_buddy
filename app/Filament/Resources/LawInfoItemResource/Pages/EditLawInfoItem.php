<?php

namespace App\Filament\Resources\LawInfoItemResource\Pages;

use App\Filament\Resources\LawInfoItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLawInfoItem extends EditRecord
{
    protected static string $resource = LawInfoItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
