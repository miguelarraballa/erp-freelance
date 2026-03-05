<?php

namespace Woocommerce\Filament\Resources\TiendasWoo\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Woocommerce\Filament\Resources\TiendasWoo\TiendaWooResource;

class ListTiendasWoo extends ListRecords
{
    protected static string $resource = TiendaWooResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
