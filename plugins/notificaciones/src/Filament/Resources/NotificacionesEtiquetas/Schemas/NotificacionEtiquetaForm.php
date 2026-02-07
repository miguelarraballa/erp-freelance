<?php

namespace Notificaciones\Filament\Resources\NotificacionesEtiquetas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;

class NotificacionEtiquetaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Información de la Etiqueta')
                    ->schema([
                        TextInput::make('tag_name')
                            ->label('Nombre de Etiqueta')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('El nombre de la etiqueta que se usará en las plantillas (ej: nombre_cliente)'),

                        TextInput::make('tag_value')
                            ->label('Valor de Etiqueta')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Tabla y campo separados por punto (ej: clientes.contacto_nombre)')
                            ->placeholder('tabla.campo')
                            ->regex('/^[a-z_]+\.[a-z_]+$/')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        // Check if value matches table.field pattern
                                        if (!preg_match('/^([a-z_]+)\.([a-z_]+)$/', $value, $matches)) {
                                            $fail('El formato debe ser tabla.campo (ej: clientes.contacto_nombre)');
                                            return;
                                        }

                                        $table = $matches[1];
                                        $field = $matches[2];

                                        // Check if table exists
                                        if (!DbSchema::hasTable($table)) {
                                            $fail("La tabla '{$table}' no existe en la base de datos.");
                                            return;
                                        }

                                        // Check if field exists in table
                                        if (!DbSchema::hasColumn($table, $field)) {
                                            $fail("El campo '{$field}' no existe en la tabla '{$table}'.");
                                            return;
                                        }
                                    };
                                },
                            ]),
                    ])
                    ->columns(1),
            ]);
    }
}
