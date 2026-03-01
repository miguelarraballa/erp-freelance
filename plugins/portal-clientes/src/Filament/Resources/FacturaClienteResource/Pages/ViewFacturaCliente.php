<?php

namespace PortalClientes\Filament\Resources\FacturaClienteResource\Pages;

use App\Models\Factura;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use PortalClientes\Filament\Resources\FacturaClienteResource;

class ViewFacturaCliente extends ViewRecord
{
    protected static string $resource = FacturaClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->url(fn () => route('portal.facturas.pdf', $this->record))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make('Datos de la factura')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('numero_completo')
                            ->label('Número'),
                        TextEntry::make('fecha')
                            ->label('Fecha')
                            ->date('d/m/Y'),
                        TextEntry::make('vencimiento')
                            ->label('Vencimiento')
                            ->date('d/m/Y'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'emitida' => 'warning',
                                'cobrada' => 'success',
                                'anulada' => 'danger',
                                default   => 'gray',
                            })
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'emitida' => 'Emitida',
                                'cobrada' => 'Cobrada',
                                'anulada' => 'Anulada',
                                default   => ucfirst($state),
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
                            ->visible(fn (Factura $record) => filled($record->notas)),
                    ]),
            ]);
    }
}
