<?php

namespace App\Filament\Resources\Pagos\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{
    TextInput,
    DatePicker,
    Select,
    FileUpload,
};
use Illuminate\Support\Carbon;
use App\Models\Factura;


class PagoForm
{
    public static function configure(Schema $schema, ?Factura $factura = null): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                ...static::facturaField($factura),
                DatePicker::make('fecha_pago')
                    ->label('Fecha del pago')
                    ->required()
                    ->default(fn () => now()->toDateString())
                    ->columnSpan(5),
                TextInput::make('importe')
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->columnSpan(2),
                FileUpload::make('justificante_path')
                    ->label('Justificante (PDF/JPG)')
                    ->disk('public')
                    ->directory('justificantes')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg'])
                    ->columnSpan(12),
            ]);
    }

    protected static function facturaField(?Factura $factura): array
    {
        if ($factura) {
            return [];
        }

        return [
            Select::make('factura_id')
                ->label('Factura')
                ->relationship(
                    name: 'factura',
                    titleAttribute: 'numero_completo',
                    modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) =>
                        $query->whereIn('estado', ['emitida','cobrada'])->orderByDesc('numero_completo'),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (Factura $record): string => (string) $record->numero_completo
                )
                ->searchable()
                ->required()
                ->columnSpan(5),
        ];
    }
}
