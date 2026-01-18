<?php

namespace Presupuestos\Filament\Resources\Presupuestos\Pages;

use Presupuestos\Filament\Resources\Presupuestos\PresupuestoResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePresupuesto extends CreateRecord
{
    protected static string $resource = PresupuestoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['estado'] = 'borrador';
        return $data;
    }
    
    protected function afterCreate(): void
    {
        \Presupuestos\Services\PresupuestoCalc::recalcular($this->record);
    }
}
