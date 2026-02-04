<?php

namespace App\Filament\Resources\Facturas\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\{Action, BulkAction, BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\{Factura, Pago};
use App\Services\FacturaCalc;
use App\Filament\Resources\Pagos\Schemas\PagoForm;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;


class FacturaPagosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

    public function form(Schema $schema): Schema
    {
        return PagoForm::configure($schema, $this->getOwnerRecord());
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_pago', 'desc')
            ->columns([
                TextColumn::make('fecha_pago')
                    ->date("d-m-Y")
                    ->sortable(),
                TextColumn::make('importe')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pdf')
                    ->label('PDF')
                    ->getStateUsing(fn (Pago $record): ?int => $record->justificante_path ? 1 : null)
                    ->formatStateUsing(fn (): string => '')
                    ->icon(fn (Pago $record) => $record->justificante_path !== null ? Heroicon::OutlinedArrowDownTray : null)
                    ->url(fn (Pago $record): ?string => $record->justificante_path !== null ? route('pagos.justificante', $record) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->tooltip(fn (Pago $record): ?string => $record->justificante_path ? 'Descargar PDF' : null)
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Añadir pago')
                    ->disabled(fn () => (bool) $this->getOwnerRecord()?->cerrado)
                    ->hidden(fn () => (bool) $this->getOwnerRecord()?->cerrado),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (Pago $record) => (bool) $record->facturado)
                    ->hidden(fn (Pago $record) => (bool) $record->facturado),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->disabled(fn (Collection $records) => $records->where('facturado', true)->count() > 0),
                ]),
            ]);
    }

}
