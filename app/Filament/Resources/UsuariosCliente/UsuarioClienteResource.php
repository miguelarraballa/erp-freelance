<?php

namespace App\Filament\Resources\UsuariosCliente;

use App\Filament\Resources\UsuariosCliente\Pages\CreateUsuarioCliente;
use App\Filament\Resources\UsuariosCliente\Pages\EditUsuarioCliente;
use App\Filament\Resources\UsuariosCliente\Pages\ListUsuariosCliente;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class UsuarioClienteResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;
    protected static \UnitEnum|string|null $navigationGroup = 'Clientes';
    protected static ?string $navigationLabel = 'Usuarios portal';
    protected static ?string $modelLabel = 'Usuario portal';
    protected static ?string $pluralModelLabel = 'Usuarios portal';
    protected static ?int $navigationSort = 13;
    protected static ?string $slug = 'usuarios-portal';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role('cliente');
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\UsuariosCliente\Schemas\UsuarioClienteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente asociado')
                    ->searchable()
                    ->placeholder('— Sin asignar —'),
                IconColumn::make('has_cliente')
                    ->label('Acceso portal')
                    ->state(fn (User $record) => $record->cliente()->exists())
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsuariosCliente::route('/'),
            'create' => CreateUsuarioCliente::route('/create'),
            'edit'   => EditUsuarioCliente::route('/{record}/edit'),
        ];
    }
}
