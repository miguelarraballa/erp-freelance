<?php

namespace Notificaciones\Filament\Resources\NotificacionesPlantillas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;

class NotificacionPlantillaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->schema([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(12),
                TextInput::make('asunto')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(12),
                RichEditor::make('cuerpo_html')
                    ->label('Cuerpo HTML')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('cuerpo_texto')
                    ->label('Cuerpo texto')
                    ->rows(8)
                    ->columnSpanFull(),
            ]);
    }
}
