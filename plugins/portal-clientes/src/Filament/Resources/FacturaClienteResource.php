<?php

namespace PortalClientes\Filament\Resources;

use App\Models\Factura;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PortalClientes\Filament\Resources\FacturaClienteResource\Pages\ListFacturasCliente;
use PortalClientes\Filament\Resources\FacturaClienteResource\Pages\ViewFacturaCliente;

class FacturaClienteResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'Mis Facturas';
    protected static ?string $pluralModelLabel = 'Facturas';
    protected static ?string $modelLabel = 'Factura';
    protected static ?string $breadcrumb = 'Mis Facturas';
    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        $clienteId = auth()->user()?->cliente?->id;

        return parent::getEloquentQuery()
            ->where('cliente_id', $clienteId)
            ->whereIn('estado', ['emitida', 'cobrada', 'anulada']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('numero_completo')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'emitida'  => 'warning',
                        'cobrada'  => 'success',
                        'anulada'  => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'emitida'  => 'Emitida',
                        'cobrada'  => 'Cobrada',
                        'anulada'  => 'Anulada',
                        default    => ucfirst($state),
                    }),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()->label('Ver'),
                \Filament\Actions\Action::make('descargar_pdf')
                    ->label('PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (Factura $record) => route('portal.facturas.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([])
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacturasCliente::route('/'),
            'view'  => ViewFacturaCliente::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
