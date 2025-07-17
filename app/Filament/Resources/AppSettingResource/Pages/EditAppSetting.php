<?php


namespace App\Filament\Resources\AppSettingResource\Pages;

use App\Filament\Resources\AppSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppSetting extends EditRecord
{
    protected static string $resource = AppSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prepare data for form based on type
        if ($data['type'] === 'boolean') {
            $data['boolean_value'] = (bool) $data['value'];
        } elseif (in_array($data['type'], ['array', 'json'])) {
            $data['array_value'] = $data['value'];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
