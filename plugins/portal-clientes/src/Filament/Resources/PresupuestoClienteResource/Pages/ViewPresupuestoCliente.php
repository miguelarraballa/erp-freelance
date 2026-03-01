<?php

namespace PortalClientes\Filament\Resources\PresupuestoClienteResource\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use PortalClientes\Filament\Resources\PresupuestoClienteResource;
use Presupuestos\Models\Presupuesto;

class ViewPresupuestoCliente extends ViewRecord
{
    protected static string $resource = PresupuestoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(fn () => route('portal.presupuestos.pdf', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make('Datos del presupuesto')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('numero_completo')
                            ->label('Número'),
                        TextEntry::make('fecha')
                            ->label('Fecha')
                            ->date('d/m/Y'),
                        TextEntry::make('vencimiento')
                            ->label('Válido hasta')
                            ->date('d/m/Y'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'emitido'     => 'warning',
                                'aceptado'    => 'success',
                                'no-aceptado' => 'danger',
                                'facturado'   => 'info',
                                default       => 'gray',
                            })
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'emitido'     => 'Emitido',
                                'aceptado'    => 'Aceptado',
                                'no-aceptado' => 'No aceptado',
                                'facturado'   => 'Facturado',
                                default       => ucfirst($state),
                            }),
                        TextEntry::make('base')
                            ->label('Base imponible')
                            ->money('EUR'),
                        TextEntry::make('iva_total')
                            ->label('IVA')
                            ->money('EUR'),
                        TextEntry::make('irpf_total')
                            ->label('IRPF')
                            ->money('EUR'),
                        TextEntry::make('total')
                            ->label('Total')
                            ->money('EUR')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('notas')
                            ->label('Notas')
                            ->columnSpanFull()
                            ->visible(fn (Presupuesto $record) => filled($record->notas)),
                    ]),
            ]);
    }
}
