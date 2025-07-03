<?php

namespace App\Filament\Resources\LawInfoItemResource\Pages;

use App\Filament\Resources\LawInfoItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLawInfoItem extends CreateRecord
{
    protected static string $resource = LawInfoItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
