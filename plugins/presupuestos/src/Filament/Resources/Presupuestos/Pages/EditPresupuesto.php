<?php

namespace Presupuestos\src\Filament\Resources\Presupuestos\Pages;

use Prespuestos\src\Filament\Resources\Presupuestos\PresupuestosResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\{Action, DeleteAction};
use App\Models\Serie;
use Presupuestos\src\Services\PresupuestoService;
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

        return [
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
            \Presupuestos\src\Services\PresupuestoCalc::recalcular($this->record);
        }

        if (! $this->shouldEmit) {
            return;
        }

        if ($this->record->estado === 'emitido' && is_null($this->record->numero)) {
            $f = PresupuestoService::emitir($this->record->fresh());

            $this->redirect($this->getResource()::getUrl('edit', ['record' => $f]));
        }
    }
}