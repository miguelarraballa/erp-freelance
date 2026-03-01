<?php

namespace PortalClientes\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PortalClientes\Filament\Resources\ProyectoClienteResource\Pages\ListProyectosCliente;
use Proyectos\Models\Proyecto;

class ProyectoClienteResource extends Resource
{
    protected static ?string $model = Proyecto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;
    protected static ?string $navigationLabel = 'Mis Proyectos';
    protected static ?string $pluralModelLabel = 'Proyectos';
    protected static ?string $modelLabel = 'Proyecto';
    protected static ?string $breadcrumb = 'Mis Proyectos';
    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        $clienteId = auth()->user()?->cliente?->id;

        return parent::getEloquentQuery()
            ->where('cliente_id', $clienteId);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_inicio', 'desc')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Proyecto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('cerrado')
                    ->label('Cerrado')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedClock)
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()->label('Ver'),
            ])
            ->toolbarActions([])
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProyectosCliente::route('/'),
            'view'  => \PortalClientes\Filament\Resources\ProyectoClienteResource\Pages\ViewProyectoCliente::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
