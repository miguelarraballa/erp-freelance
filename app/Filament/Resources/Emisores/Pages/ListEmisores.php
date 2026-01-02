<?php

namespace App\Filament\Resources\Emisores\Pages;

use App\Filament\Resources\Emisores\EmisorResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmisores extends ListRecords
{
    protected static string $resource = EmisorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getEmptyStateActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear emisor'),
        ];
    }

}