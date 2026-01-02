<?php

namespace App\Filament\Resources\Emisores;

use App\Filament\Resources\Emisores\Pages\{EditEmisor, ListEmisores, CreateEmisor};
use App\Filament\Resources\Emisores\Schemas\EmisorForm;
use App\Filament\Resources\Emisores\Tables\EmisoresTable;
use App\Models\Emisor;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmisorResource extends Resource
{
    protected static ?string $model = Emisor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static UnitEnum|string|null $navigationGroup = 'Empresa';
    protected static ?int $navigationSort = 1;

     protected static ?string $slug = 'emisores';  
    protected static ?string $modelLabel = 'Emisor';
    protected static ?string $pluralModelLabel = 'Emisores';

    public static function form(Schema $schema): Schema
    {
        return EmisorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmisoresTable::configure($table);
    }

    public static function canDelete(Model $record): bool
    {
        return false; // no borramos el emisor
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEmisores::route('/'),
            'create' => CreateEmisor::route('/create'),
            'edit'   => EditEmisor::route('/{record}/edit'),
        ];
    }
}