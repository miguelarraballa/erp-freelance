<?php

namespace Gastos\Filament\Resources\Gastos\Pages;

use Filament\Resources\Pages\CreateRecord;
use Gastos\Filament\Resources\Gastos\GastoResource;

class CreateGasto extends CreateRecord
{
    protected static string $resource = GastoResource::class;
}
