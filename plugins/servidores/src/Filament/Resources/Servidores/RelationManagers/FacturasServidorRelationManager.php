<?php

namespace Servidores\Filament\Resources\Servidores\RelationManagers;

use App\Filament\Resources\Facturas\FacturaResource;
use App\Models\Factura;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacturasServidorRelationManager extends RelationManager
{
    protected static string $relationship = 'facturas';

    protected static ?string $title = 'Facturas asociadas';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha', 'desc')
            ->columns([
                TextColumn::make('numero_completo')
                    ->label('Nº Factura')
                    ->searchable()
                    ->placeholder('Borrador'),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'borrador'  => 'gray',
                        'emitida'   => 'info',
                        'cobrada'   => 'success',
                        'anulada'   => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('editar')
                    ->label('Abrir')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Factura $record): string => FacturaResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([]);
    }
}
