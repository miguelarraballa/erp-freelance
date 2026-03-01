<?php

namespace PortalClientes\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Presupuestos\Models\Presupuesto;
use PortalClientes\Filament\Resources\PresupuestoClienteResource\Pages\ListPresupuestosCliente;
use PortalClientes\Filament\Resources\PresupuestoClienteResource\Pages\ViewPresupuestoCliente;

class PresupuestoClienteResource extends Resource
{
    protected static ?string $model = Presupuesto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Mis Presupuestos';
    protected static ?string $pluralModelLabel = 'Presupuestos';
    protected static ?string $modelLabel = 'Presupuesto';
    protected static ?string $breadcrumb = 'Mis Presupuestos';
    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        $clienteId = auth()->user()?->cliente?->id;

        return parent::getEloquentQuery()
            ->where('cliente_id', $clienteId)
            ->whereIn('estado', ['emitido', 'aceptado', 'no-aceptado', 'facturado']);
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
                    ->label('Válido hasta')
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
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()->label('Ver'),
                \Filament\Actions\Action::make('descargar_pdf')
                    ->label('PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (Presupuesto $record) => route('portal.presupuestos.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([])
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPresupuestosCliente::route('/'),
            'view'  => ViewPresupuestoCliente::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
