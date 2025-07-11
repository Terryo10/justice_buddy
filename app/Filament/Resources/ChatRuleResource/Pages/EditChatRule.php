<?php

namespace App\Filament\Resources\ChatRuleResource\Pages;

use App\Filament\Resources\ChatRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChatRule extends EditRecord
{
    protected static string $resource = ChatRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
