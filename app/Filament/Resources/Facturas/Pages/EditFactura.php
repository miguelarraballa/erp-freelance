<?php

namespace App\Filament\Resources\Facturas\Pages;

use App\Filament\Resources\Facturas\FacturaResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\{Action, DeleteAction};
use App\Models\Serie;
use App\Services\FacturaService;
use App\Services\FacturaLogger;
use Illuminate\Support\Carbon;
use Notificaciones\Helpers\NotificacionesHelper;

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

            // Encolar notificación de factura emitida
            try {
                // Cargar la relación cliente si no está cargada
                $f->load('cliente');

                // Priorizar facturacion_email, si no existe usar contacto_email
                $emailCliente = $f->cliente->facturacion_email ?? $f->cliente->contacto_email ?? null;

                \Log::info('Intentando encolar notificación de factura', [
                    'factura_id' => $f->id,
                    'cliente_id' => $f->cliente_id,
                    'email_cliente' => $emailCliente,
                    'facturacion_email' => $f->cliente->facturacion_email,
                    'contacto_email' => $f->cliente->contacto_email,
                ]);

                if (!$emailCliente) {
                    \Log::warning('Cliente sin email - no se puede enviar notificación', [
                        'factura_id' => $f->id,
                        'cliente_id' => $f->cliente_id,
                    ]);
                } elseif (!filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
                    \Log::warning('Email del cliente no válido', [
                        'factura_id' => $f->id,
                        'email' => $emailCliente,
                    ]);
                } else {
                    // Email válido, proceder a encolar
                    // Obtener el emisor activo
                    $emisorActivo = \DB::table('emisores')->where('activo', true)->value('id');

                    $notificacion = NotificacionesHelper::queueEmail(
                        plantillaNombre: 'factura_emitida',
                        emailDestinatario: $emailCliente,
                        context: [
                            'facturas' => $f->id,
                            'clientes' => $f->cliente_id,
                            'emisores' => $emisorActivo, // Incluir emisor activo
                        ],
                        relacionadoTabla: 'facturas',
                        relacionadoId: $f->id,
                        adjuntable: $f
                    );

                    \Log::info('Notificación encolada exitosamente', [
                        'factura_id' => $f->id,
                        'notificacion_id' => $notificacion->id,
                        'email' => $emailCliente,
                    ]);
                }

            } catch (\Exception $e) {
                // Log error but don't stop the process
                \Log::error('Error al encolar notificación de factura emitida', [
                    'factura_id' => $f->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $this->redirect($this->getResource()::getUrl('edit', ['record' => $f]));
        }
    }
}