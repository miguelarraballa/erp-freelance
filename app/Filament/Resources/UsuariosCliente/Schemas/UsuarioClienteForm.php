<?php

namespace App\Filament\Resources\UsuariosCliente\Schemas;

use App\Models\Cliente;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UsuarioClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Section::make('Datos de acceso')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email de acceso')
                            ->email()
                            ->required()
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->rule(Password::min(8)->letters()->numbers())
                            ->helperText(fn (string $operation) => $operation === 'edit'
                                ? 'Deja en blanco para no cambiar la contraseña.'
                                : 'Mínimo 8 caracteres, letras y números.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Cliente del portal')
                    ->icon('heroicon-o-building-office')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Asociar a cliente')
                            ->helperText('Selecciona el cliente cuyas facturas y proyectos verá este usuario en el portal.')
                            ->options(function (?User $record) {
                                return Cliente::query()
                                    ->where('cliente', true)
                                    ->where('activo', true)
                                    ->whereNotNull('nombre')
                                    ->where('nombre', '!=', '')
                                    ->where(function ($q) use ($record) {
                                        $q->whereNull('user_id')
                                          ->orWhere('user_id', $record?->id);
                                    })
                                    ->orderBy('nombre')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [$c->id => $c->nombre . ($c->nif ? ' · ' . $c->nif : '')]);
                            })
                            ->afterStateHydrated(function ($set, ?User $record) {
                                $set('cliente_id', $record?->cliente?->id);
                            })
                            ->searchable()
                            ->nullable(),
                    ]),
            ]);
    }
}
