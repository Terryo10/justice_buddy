<?php

namespace App\Filament\Resources\ChatRuleResource\Pages;

use App\Filament\Resources\ChatRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatRules extends ListRecords
{
    protected static string $resource = ChatRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
