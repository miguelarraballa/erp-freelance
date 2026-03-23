<?php

namespace AnexoRgpd\Filament\Resources\AnexoRgpd\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use AnexoRgpd\Filament\Resources\AnexoRgpd\AnexoRgpdResource;
use AnexoRgpd\Models\AnexoRgpd;

class EditAnexoRgpd extends EditRecord
{
    protected static string $resource = AnexoRgpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (AnexoRgpd $record) => route('anexo-rgpd.pdf', $record))
                ->openUrlInNewTab(),

            DeleteAction::make(),
        ];
    }
}
