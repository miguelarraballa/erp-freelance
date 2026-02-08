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
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'blockquote',
                        'bulletList',
                        'orderedList',
                        'link',
                        'undo',
                        'redo',
                    ]),
                Textarea::make('html_personalizado')
                    ->label('HTML Personalizado (Pie de Página / Avisos Legales)')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('Aquí puedes añadir HTML personalizado como <small>texto legal</small> que se agregará al final del email')
                    ->placeholder('<small>Este es un mensaje automático. Por favor no responder a este email.</small>'),
                Textarea::make('cuerpo_texto')
                    ->label('Cuerpo texto')
                    ->rows(8)
                    ->columnSpanFull(),
            ]);
    }
}
