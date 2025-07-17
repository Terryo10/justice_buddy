<?php

namespace App\Filament\Resources\AppSettingResource\Pages;

use App\Filament\Resources\AppSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppSetting extends CreateRecord
{
    protected static string $resource = AppSettingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle different value types
        if ($data['type'] === 'boolean') {
            $data['value'] = isset($data['boolean_value']) && $data['boolean_value'] ? '1' : '0';
        } elseif (in_array($data['type'], ['array', 'json'])) {
            $data['value'] = $data['array_value'] ?? '[]';
        }

        // Remove temporary fields
        unset($data['boolean_value'], $data['array_value']);

        return $data;
    }
}

