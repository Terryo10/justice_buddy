<?php

namespace App\Filament\Resources\LawyerResource\Pages;

use App\Filament\Resources\LawyerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLawyer extends EditRecord
{
    protected static string $resource = LawyerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->action(function (): void {
                    $this->record->update([
                        'is_verified' => true,
                        'verified_at' => now(),
                        'verified_by' => auth()->user()->name ?? 'System',
                    ]);
                    
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                })
                ->requiresConfirmation()
                ->visible(fn (): bool => !$this->record->is_verified),
            
            Actions\Action::make('unverify')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->action(function (): void {
                    $this->record->update([
                        'is_verified' => false,
                        'verified_at' => null,
                        'verified_by' => null,
                    ]);
                    
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                })
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->is_verified),
            
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
