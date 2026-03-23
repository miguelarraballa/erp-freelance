<?php

namespace AnexoRgpd\Filament\Resources\AnexoRgpd;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use AnexoRgpd\Models\AnexoRgpd;
use AnexoRgpd\Filament\Resources\AnexoRgpd\Schemas\AnexoRgpdForm;
use AnexoRgpd\Filament\Resources\AnexoRgpd\Tables\AnexosRgpdTable;
use AnexoRgpd\Filament\Resources\AnexoRgpd\Pages\ListAnexosRgpd;
use AnexoRgpd\Filament\Resources\AnexoRgpd\Pages\CreateAnexoRgpd;
use AnexoRgpd\Filament\Resources\AnexoRgpd\Pages\EditAnexoRgpd;
use Filament\Support\Icons\Heroicon;

class AnexoRgpdResource extends Resource
{
    protected static ?string $model = AnexoRgpd::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static \UnitEnum|string|null $navigationGroup = 'Clientes';

    protected static ?string $navigationLabel = 'Anexo RGPD';

    protected static ?string $modelLabel = 'Anexo RGPD';

    protected static ?string $pluralModelLabel = 'Anexos RGPD';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return AnexoRgpdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnexosRgpdTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAnexosRgpd::route('/'),
            'create' => CreateAnexoRgpd::route('/create'),
            'edit'   => EditAnexoRgpd::route('/{record}/edit'),
        ];
    }
}
