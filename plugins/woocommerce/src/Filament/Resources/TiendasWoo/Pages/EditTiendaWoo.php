<?php

namespace Woocommerce\Filament\Resources\TiendasWoo\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Woocommerce\Filament\Resources\TiendasWoo\TiendaWooResource;

class EditTiendaWoo extends EditRecord
{
    protected static string $resource = TiendaWooResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
