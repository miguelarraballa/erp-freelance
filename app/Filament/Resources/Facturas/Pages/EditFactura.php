<?php

namespace App\Filament\Resources\Facturas\Pages;

use App\Filament\Resources\Facturas\FacturaResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\{Action, DeleteAction};
use App\Models\Serie;
use App\Services\FacturaService;
use App\Services\FacturaLogger;
use Illuminate\Support\Carbon;

class EditFactura extends EditRecord
{
    protected static string $resource = FacturaResource::class;

    protected bool $shouldEmit = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->shouldEmit = ($this->record->estado === 'borrador')
            && (($data['estado'] ?? 'borrador') === 'emitida');

        if ($this->shouldEmit) {
            $ejercicio = (int) Carbon::parse($data['fecha'] ?? now()->toDateString())->year;

            $serie = Serie::query()
                ->where('tipo', $data['tipo'] ?? $this->record->tipo)
                ->where('ejercicio', $ejercicio)
                ->where('activo', true)
                ->firstOrFail();

            $data['serie_id'] = $serie->id;
        }

        return $data;
    }

    /** Botones del formulario (pie de página) */
    protected function getFormActions(): array
    {
        $enBorrador = fn () => $this->record?->estado === 'borrador';

        return [
            // Helper propio de la página (no hay clase SaveAction)
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    /** Acciones de cabecera */
    protected function getHeaderActions(): array
    {
        $enBorrador = fn () => $this->record?->estado === 'borrador';

        return [
             Action::make('pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('facturas.pdf', $this->record))
                ->openUrlInNewTab(),

            DeleteAction::make()->hidden(fn () => ! $enBorrador()),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->estado === 'borrador') {
            \App\Services\FacturaCalc::recalcular($this->record);
        }

        if (! $this->shouldEmit) {
            return;
        }

        if ($this->record->estado === 'emitida' && is_null($this->record->numero)) {
            $f = FacturaService::emitir($this->record->fresh());

            FacturaLogger::log(
                $f,
                'emitida',
                [
                    'serie_id'        => $f->serie_id,
                    'numero'          => $f->numero,
                    'numero_completo' => $f->numero_completo,
                    'base'            => $f->base,
                    'iva_total'       => $f->iva_total,
                    'irpf_total'      => $f->irpf_total,
                    'total'           => $f->total,
                ],
                auth()->id(),
                request()->ip(),
                request()->userAgent()
            );

            $this->redirect($this->getResource()::getUrl('edit', ['record' => $f]));
        }
    }
}