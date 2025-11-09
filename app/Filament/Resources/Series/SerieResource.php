<?php

namespace App\Filament\Resources\Series;

use App\Filament\Resources\Series\Pages\CreateSerie;
use App\Filament\Resources\Series\Pages\EditSerie;
use App\Filament\Resources\Series\Pages\ListSeries;
use App\Filament\Resources\Series\Schemas\SerieForm;
use App\Filament\Resources\Series\Tables\SeriesTable;
use App\Models\Serie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SerieResource extends Resource
{
    protected static ?string $model = Serie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Facturacion';
    protected static ?int $navigationSort = 20;
    protected static ?string $recordTitleAttribute = 'Serie';

    public static function form(Schema $schema): Schema
    {
        return SerieForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeries::route('/'),
            'create' => CreateSerie::route('/create'),
            'edit' => EditSerie::route('/{record}/edit'),
        ];
    }
}
