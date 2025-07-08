<?php

namespace App\Filament\Resources\LawyerResource\Pages;

use App\Filament\Resources\LawyerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLawyer extends CreateRecord
{
    protected static string $resource = LawyerResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug(
                $data['first_name'] . ' ' . $data['last_name'] . ' ' . $data['firm_name']
            );
        }

        return $data;
    }
}
