<?php

namespace App\Filament\Resources\FacturasProveedores\Pages;

use App\Filament\Resources\FacturasProveedores\FacturasProveedorResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Serie;
use App\Models\FacturaProveedor;
use App\Services\FacturaProveedorService;
use App\Services\SerieNumeracionService;
use Illuminate\Support\Carbon;  


class CreateFacturasProveedor extends CreateRecord
{
    protected static string $resource = FacturasProveedorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $ejercicio = (int) Carbon::parse($data['fecha'] ?? now()->toDateString())->year;

        $serie = Serie::query()
            ->where('tipo', 'proveedor')
            ->where('ejercicio', $ejercicio)
            ->where('activo', true)
            ->firstOrFail();
    
        if (!$serie) {
            throw new \DomainException('No existe una serie activa para facturas de proveedor en el ejercicio.');
        }

        $seq = (int) $serie->siguiente_numero;

        $numeroCompleto = SerieNumeracionService::renderNumeroCompleto($serie, $seq);

        // Avanzar contador de serie

        $serie->increment('siguiente_numero');

        // Asignar datos en factura
        $data['serie_id'] = $serie->id;
        $data['numero'] = $seq;
        $data['numero_completo'] = $numeroCompleto; 

        return $data;

    }

    protected function afterSave(): void
    {
        
        if (is_null($this->record->numero)) {
            $f = FacturaProveedorService::emitir($this->record->fresh());

            $this->redirect($this->getResource()::getUrl('edit', ['record' => $f]));
        }

    }

}
