<?php

namespace App\Filament\Resources\LetterRequestResource\Pages;

use App\Filament\Resources\LetterRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLetterRequest extends CreateRecord
{
    protected static string $resource = LetterRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
