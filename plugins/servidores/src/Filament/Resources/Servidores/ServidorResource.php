<?php

namespace Servidores\Filament\Resources\Servidores;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Servidores\Filament\Resources\Servidores\Pages\CreateServidor;
use Servidores\Filament\Resources\Servidores\Pages\EditServidor;
use Servidores\Filament\Resources\Servidores\Pages\ListServidores;
use Servidores\Filament\Resources\Servidores\RelationManagers\FacturasServidorRelationManager;
use Servidores\Filament\Resources\Servidores\Schemas\ServidorForm;
use Servidores\Filament\Resources\Servidores\Tables\ServidoresTable;
use Servidores\Models\Servidor;

class ServidorResource extends Resource
{
    protected static ?string $model = Servidor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;
    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?string $navigationLabel = 'Servidores';
    protected static ?int $navigationSort = 60;

    protected static ?string $pluralModelLabel = 'Servidores';
    protected static ?string $modelLabel = 'Servidor';
    protected static ?string $breadcrumb = 'Servidores';

    protected static ?string $slug = 'servidores';
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return ServidorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServidoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FacturasServidorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListServidores::route('/'),
            'create' => CreateServidor::route('/create'),
            'edit'   => EditServidor::route('/{record}/edit'),
        ];
    }
}
