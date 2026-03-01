<?php

namespace PortalClientes\Filament\Resources\ProyectoClienteResource\Pages;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use PortalClientes\Filament\Resources\ProyectoClienteResource;
use Proyectos\Models\Proyecto;

class ViewProyectoCliente extends ViewRecord
{
    protected static string $resource = ProyectoClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make('Datos del proyecto')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Proyecto')
                            ->columnSpanFull(),
                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->visible(fn (Proyecto $record) => filled($record->descripcion)),
                        TextEntry::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->date('d/m/Y'),
                        TextEntry::make('fecha_fin')
                            ->label('Fecha de fin')
                            ->date('d/m/Y'),
                        IconEntry::make('cerrado')
                            ->label('Estado')
                            ->boolean()
                            ->trueIcon(Heroicon::OutlinedCheckCircle)
                            ->falseIcon(Heroicon::OutlinedClock)
                            ->trueColor('success')
                            ->falseColor('warning'),
                    ]),
            ]);
    }
}
