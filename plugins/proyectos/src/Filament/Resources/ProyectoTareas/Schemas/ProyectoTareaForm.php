<?php

namespace Proyectos\Filament\Resources\ProyectoTareas\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\{DatePicker, Select, Textarea, TextInput, TimePicker, Toggle};
use Filament\Schemas\Components\Utilities\{Get, Set};
use Proyectos\Models\Proyecto;
use Closure;

class ProyectoTareaForm
{
    public static function configure(Schema $schema, ?Proyecto $proyecto = null): Schema
    {
        $resolveProyecto = function (Get $get) use ($proyecto): ?Proyecto {
            if ($proyecto) {
                return $proyecto;
            }

            $id = $get('proyecto_id');
            if (! $id) {
                return null;
            }

            return Proyecto::find((int) $id);
        };

        return $schema
            ->columns(12)
            ->schema([
                ...static::proyectoField($proyecto),

                Textarea::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->rows(4)
                    ->columnSpan(12)
                    ->disabled(fn ($record) => (bool) $record?->facturado),

                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->default(fn () => now()->toDateString())
                    ->minDate(fn (Get $get) => $resolveProyecto($get)?->fecha_inicio?->toDateString())
                    ->maxDate(fn (Get $get) => $resolveProyecto($get)?->fecha_fin?->toDateString())
                    ->rules([
                        fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($resolveProyecto, $get) {
                            $p = $resolveProyecto($get);
                            if (! $p || ! $value) {
                                return;
                            }

                            $fecha = \Illuminate\Support\Carbon::parse($value)->startOfDay();
                            if ($p->fecha_inicio && $fecha->lt($p->fecha_inicio)) {
                                $fail('La fecha debe ser posterior o igual a la fecha de inicio del proyecto.');
                            }
                            if ($p->fecha_fin && $fecha->gt($p->fecha_fin)) {
                                $fail('La fecha debe ser anterior o igual a la fecha de fin del proyecto.');
                            }
                        },
                    ])
                    ->required()
                    ->columnSpan(4)
                    ->disabled(fn ($record) => (bool) $record?->facturado),

                TimePicker::make('inicio')
                    ->label('Inicio')
                    ->seconds(false)
                    ->default(fn () => now()->format('H:i'))
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        static::ensureFinAfterInicio($get, $set);
                        static::syncDuracionFromHoras($get, $set);
                    })
                    ->required()
                    ->columnSpan(4)
                    ->disabled(fn ($record) => (bool) $record?->facturado),

                TimePicker::make('fin')
                    ->label('Fin')
                    ->seconds(false)
                    ->default(fn () => now()->addHour()->format('H:i'))
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        static::syncDuracionFromHoras($get, $set);
                    })
                    ->rules([
                        fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                            $inicio = static::timeToMinutes($get('inicio'));
                            $fin = static::timeToMinutes($value);
                            if ($inicio === null || $fin === null) {
                                return;
                            }
                            if ($fin < $inicio) {
                                $fail('La hora de fin no puede ser anterior a la de inicio.');
                            }
                        },
                    ])
                    ->required()
                    ->columnSpan(4)
                    ->disabled(fn ($record) => (bool) $record?->facturado),

                TextInput::make('duracion')
                    ->label('Duración (h)')
                    ->numeric()
                    ->step('0.01')
                    ->default(1.00)
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        static::syncFinFromDuracion($get, $set);
                    })
                    ->required()
                    ->columnSpan(4)
                    ->disabled(fn ($record) => (bool) $record?->facturado),

                TextInput::make('precio')
                    ->label('Precio / h')
                    ->numeric()
                    ->step('0.01')
                    ->default(15.00)
                    ->required()
                    ->columnSpan(4)
                    ->disabled(fn ($record) => (bool) $record?->facturado),

                Toggle::make('facturado')
                    ->label('Facturado')
                    ->default(false)
                    ->columnSpan(4),
            ]);
    }

    protected static function proyectoField(?Proyecto $proyecto): array
    {
        if ($proyecto) {
            return [];
        }

        return [
            Select::make('proyecto_id')
                ->label('Proyecto')
                ->relationship(
                    name: 'proyecto',
                    titleAttribute: 'nombre',
                    modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) =>
                        $query->where('cerrado', false)
                )
                ->getOptionLabelUsing(fn ($value) => Proyecto::find($value)?->nombre)
                ->searchable()
                ->preload()
                ->required()
                ->columnSpan(12)
                ->disabled(fn ($record) => (bool) $record?->facturado),
        ];
    }

    protected static function ensureFinAfterInicio(Get $get, Set $set): void
    {
        $inicio = static::timeToMinutes($get('inicio'));
        $fin = static::timeToMinutes($get('fin'));
        if ($inicio === null || $fin === null) {
            return;
        }
        if ($fin < $inicio) {
            $set('fin', static::minutesToTime($inicio));
        }
    }

    protected static function syncDuracionFromHoras(Get $get, Set $set): void
    {
        $inicio = static::timeToMinutes($get('inicio'));
        $fin = static::timeToMinutes($get('fin'));
        if ($inicio === null || $fin === null || $fin < $inicio) {
            return;
        }

        $duracion = round(($fin - $inicio) / 60, 2);
        $set('duracion', $duracion);
    }

    protected static function syncFinFromDuracion(Get $get, Set $set): void
    {
        $inicio = static::timeToMinutes($get('inicio'));
        $duracion = $get('duracion');
        if ($inicio === null || $duracion === null || $duracion === '') {
            return;
        }

        $minutos = $inicio + (int) round(((float) $duracion) * 60);
        if ($minutos > 1439) {
            $minutos = 1439;
        }

        $set('fin', static::minutesToTime($minutos));
    }

    protected static function timeToMinutes(?string $time): ?int
    {
        if (! $time) {
            return null;
        }

        $parts = explode(':', $time);
        if (count($parts) < 2) {
            return null;
        }

        return ((int) $parts[0]) * 60 + (int) $parts[1];
    }

    protected static function minutesToTime(int $minutes): string
    {
        $minutes = max(0, min(1439, $minutes));
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }
}
