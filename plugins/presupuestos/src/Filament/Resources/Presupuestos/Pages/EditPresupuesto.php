<?php

namespace Presupuestos\Filament\Resources\Presupuestos\Pages;

use Presupuestos\Filament\Resources\Presupuestos\PresupuestoResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\{Action, DeleteAction};
use App\Models\Serie;
use Presupuestos\Services\PresupuestoService;
use Presupuestos\Services\PresupuestoCalc;
use Illuminate\Support\Carbon;

class EditPresupuesto extends EditRecord
{
    protected static string $resource = PresupuestoResource::class;

    protected bool $shouldEmit = false;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->shouldEmit = ($this->record->estado === 'borrador')
            && (($data['estado'] ?? 'borrador') === 'emitido');

        if ($this->shouldEmit) {
            $ejercicio = (int) Carbon::parse($data['fecha'] ?? now()->toDateString())->year;

            $serie = Serie::query()
                ->where('tipo', 'presupuesto')
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
        $aceptado = fn () => $this->record?->estado === 'aceptado';

        return [

            Action::make('facturar')
                ->label('Crear factura')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->hidden(fn () => ! $aceptado())
                ->url(fn () => route('presupuesto.facturar', $this->record)),

             Action::make('pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('presupuesto.pdf', $this->record))
                ->openUrlInNewTab(),

            DeleteAction::make()->hidden(fn () => ! $enBorrador()),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->estado === 'borrador') {
            PresupuestoCalc::recalcular($this->record);
        }

        if (! $this->shouldEmit) {
            $this->record = $this->record->fresh(['lineas.impuesto']);
            $this->fillForm();

            return;
        }

        if ($this->record->estado === 'emitido' && is_null($this->record->numero)) {
            $f = PresupuestoService::emitir($this->record->fresh());

            $this->redirect($this->getResource()::getUrl('edit', ['record' => $f]));
        }
    }
}
