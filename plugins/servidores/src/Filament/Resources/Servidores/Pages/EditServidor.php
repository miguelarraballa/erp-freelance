<?php

namespace Servidores\Filament\Resources\Servidores\Pages;

use App\Filament\Resources\Facturas\FacturaResource;
use App\Models\Factura;
use App\Models\FacturaLinea;
use App\Models\Impuesto;
use App\Services\FacturaCalc;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Servidores\Filament\Resources\Servidores\ServidorResource;
use Servidores\Models\Servidor;

class EditServidor extends EditRecord
{
    protected static string $resource = ServidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear_factura')
                ->label('Crear factura')
                ->icon(Heroicon::OutlinedDocumentPlus)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Crear factura')
                ->modalDescription(function (): string {
                    /** @var Servidor $record */
                    $record = $this->record;
                    return sprintf(
                        'Se creará una factura en borrador para "%s" con el concepto "%s" por %.2f €.',
                        $record->cliente->nombre ?? $record->cliente->razon_social,
                        $record->paquete ?? $record->nombre . ' — ' . $record->dominio,
                        (float) $record->precio
                    );
                })
                ->modalSubmitActionLabel('Crear factura')
                ->action(function (): void {
                    /** @var Servidor $record */
                    $record = $this->record;

                    $impuesto = Impuesto::where('tipo', 'iva')
                        ->where('porcentaje', 21)
                        ->where('activo', true)
                        ->first()
                        ?? Impuesto::where('tipo', 'iva')->where('activo', true)->first();

                    $c = $record->cliente;
                    $datosFacturacion = implode("\n", array_filter([
                        $c->razon_social ?: $c->nombre,
                        str_replace('\n', ' ', $c->direccion ?? ''),
                        trim(($c->cp ? "{$c->cp} " : '') . ($c->ciudad ?: '')),
                        ($c->provincia ? "{$c->provincia} " : '') . ($c->pais ?? ''),
                        $c->nif ? $c->nif : null,
                    ]));

                    $factura = Factura::create([
                        'cliente_id'        => $record->cliente_id,
                        'datos_facturacion' => $datosFacturacion ?: null,
                        'fecha'             => now()->toDateString(),
                        'estado'            => 'borrador',
                        'tipo'              => 'normal',
                        'base'              => 0,
                        'iva_total'         => 0,
                        'irpf_total'        => 0,
                        'total'             => 0,
                        'notas'             => 'Servidor: ' . $record->nombre . ' | Dominio: ' . $record->dominio,
                    ]);

                    $record->facturas()->attach($factura->id);

                    $concepto = $record->paquete
                        ? $record->paquete . ' — ' . $record->dominio
                        : $record->nombre . ' — ' . $record->dominio;

                    FacturaLinea::create([
                        'factura_id'      => $factura->id,
                        'orden'           => 1,
                        'concepto'        => $concepto,
                        'cantidad'        => 1,
                        'precio_unitario' => (float) $record->precio,
                        'descuento_pct'   => 0,
                        'impuesto_id'     => $impuesto?->id,
                        'base_linea'      => 0,
                        'iva_linea'       => 0,
                        'irpf_linea'      => 0,
                        'total_linea'     => 0,
                    ]);

                    FacturaCalc::recalcular($factura->fresh(['lineas.impuesto']));

                    Notification::make()
                        ->title('Factura creada en borrador')
                        ->success()
                        ->send();

                    $this->redirect(FacturaResource::getUrl('edit', ['record' => $factura]));
                }),

            DeleteAction::make(),
        ];
    }
}
