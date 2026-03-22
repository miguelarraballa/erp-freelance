<?php

namespace Informes\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Informes\Filament\Resources\InformeResource\Pages\CreateInforme;
use Informes\Filament\Resources\InformeResource\Pages\EditInforme;
use Informes\Filament\Resources\InformeResource\Pages\ListInformes;
use Informes\Filament\Resources\InformeResource\Pages\ViewInforme;
use Informes\Models\Informe;

class InformeResource extends Resource
{
    protected static ?string $model = Informe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static \UnitEnum|string|null $navigationGroup = 'Empresa';

    protected static ?string $navigationLabel = 'Informes';

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Forms\Components\TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            \Filament\Forms\Components\Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('graficas_count')
                    ->counts('graficas')
                    ->label('Gráficas')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordAction('view')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
            'index'  => ListInformes::route('/'),
            'create' => CreateInforme::route('/create'),
            'edit'   => EditInforme::route('/{record}/edit'),
            'view'   => ViewInforme::route('/{record}'),
        ];
    }
}
