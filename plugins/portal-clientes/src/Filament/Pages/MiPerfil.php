<?php

namespace PortalClientes\Filament\Pages;

use App\Models\Cliente;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use PortalClientes\Mail\EmailCambioVerificacion;

class MiPerfil extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;
    protected static ?string $navigationLabel = 'Mi Perfil';
    protected static ?string $title = 'Mi Perfil';
    protected static ?string $slug = 'mi-perfil';
    protected static ?int $navigationSort = 99;

    public function getView(): string
    {
        return 'portal-clientes::filament.pages.mi-perfil';
    }

    // Datos de facturación
    public ?array $datosData = [];

    // Email
    public string $emailNuevo = '';

    // Contraseña
    public string $passwordActual = '';
    public string $passwordNueva = '';
    public string $passwordConfirmacion = '';

    public function mount(): void
    {
        $cliente = Auth::user()?->cliente;

        $this->datosForm->fill([
            'nombre'              => $cliente?->nombre,
            'razon_social'        => $cliente?->razon_social,
            'nif'                 => $cliente?->nif,
            'direccion'           => $cliente?->direccion,
            'cp'                  => $cliente?->cp,
            'ciudad'              => $cliente?->ciudad,
            'provincia'           => $cliente?->provincia,
            'pais'                => $cliente?->pais,
            'contacto_nombre'     => $cliente?->contacto_nombre,
            'contacto_email'      => $cliente?->contacto_email,
            'contacto_telefono'   => $cliente?->contacto_telefono,
            'email_facturacion'   => $cliente?->email_facturacion,
            'telefono_facturacion'=> $cliente?->telefono_facturacion,
            'iban'                => $cliente?->iban,
        ]);
    }

    protected function getForms(): array
    {
        return ['datosForm'];
    }

    public function datosForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->label('Nombre / Razón social')
                    ->required()
                    ->maxLength(255),
                TextInput::make('razon_social')
                    ->label('Razón social completa')
                    ->maxLength(255),
                TextInput::make('nif')
                    ->label('NIF / CIF')
                    ->maxLength(20),
                TextInput::make('direccion')
                    ->label('Dirección')
                    ->maxLength(255),
                TextInput::make('cp')
                    ->label('Código postal')
                    ->maxLength(10),
                TextInput::make('ciudad')
                    ->label('Ciudad')
                    ->maxLength(100),
                TextInput::make('provincia')
                    ->label('Provincia')
                    ->maxLength(100),
                TextInput::make('pais')
                    ->label('País')
                    ->maxLength(100),
                TextInput::make('contacto_nombre')
                    ->label('Nombre de contacto')
                    ->maxLength(255),
                TextInput::make('contacto_email')
                    ->label('Email de contacto')
                    ->email()
                    ->maxLength(255),
                TextInput::make('contacto_telefono')
                    ->label('Teléfono de contacto')
                    ->tel()
                    ->maxLength(30),
                TextInput::make('email_facturacion')
                    ->label('Email de facturación')
                    ->email()
                    ->maxLength(255),
                TextInput::make('telefono_facturacion')
                    ->label('Teléfono de facturación')
                    ->tel()
                    ->maxLength(30),
                TextInput::make('iban')
                    ->label('IBAN')
                    ->maxLength(34),
            ])
            ->columns(2)
            ->key('datosForm')
            ->statePath('datosData');
    }

    public function guardarDatos(): void
    {
        $data = $this->datosForm->getState();

        $cliente = Auth::user()?->cliente;

        if (!$cliente) {
            Notification::make()
                ->title('No se encontró un cliente asociado a tu cuenta.')
                ->danger()
                ->send();
            return;
        }

        $cliente->update($data);

        Notification::make()
            ->title('Datos de facturación actualizados correctamente.')
            ->success()
            ->send();
    }

    public function cambiarEmailAction(): Action
    {
        return Action::make('cambiarEmail')
            ->label('Cambiar email')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('info')
            ->form([
                TextInput::make('email_nuevo')
                    ->label('Nuevo email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->different('email_actual')
                    ->rules(['email:rfc,dns']),
            ])
            ->action(function (array $data): void {
                $user = Auth::user();
                $emailNuevo = $data['email_nuevo'];

                if ($emailNuevo === $user->email) {
                    Notification::make()
                        ->title('El nuevo email es igual al actual.')
                        ->warning()
                        ->send();
                    return;
                }

                $token = Str::random(64);
                $expira = now()->addHours(24);

                $user->update([
                    'email_pending'            => $emailNuevo,
                    'email_pending_token'       => hash('sha256', $token),
                    'email_pending_expires_at'  => $expira,
                ]);

                try {
                    Mail::to($emailNuevo)->send(new EmailCambioVerificacion($user, $token));

                    Notification::make()
                        ->title('Correo de verificación enviado a ' . $emailNuevo)
                        ->body('Haz clic en el enlace del correo para confirmar el cambio. El enlace caduca en 24 horas.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    $user->update([
                        'email_pending'           => null,
                        'email_pending_token'      => null,
                        'email_pending_expires_at' => null,
                    ]);

                    Notification::make()
                        ->title('No se pudo enviar el correo de verificación.')
                        ->body('Inténtalo de nuevo más tarde.')
                        ->danger()
                        ->send();
                }
            });
    }

    public function cambiarPasswordAction(): Action
    {
        return Action::make('cambiarPassword')
            ->label('Cambiar contraseña')
            ->icon(Heroicon::OutlinedLockClosed)
            ->color('warning')
            ->form([
                TextInput::make('password_actual')
                    ->label('Contraseña actual')
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('password_nueva')
                    ->label('Nueva contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::min(8)->letters()->numbers()),
                TextInput::make('password_confirmacion')
                    ->label('Confirmar nueva contraseña')
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('password_nueva'),
            ])
            ->action(function (array $data): void {
                $user = Auth::user();

                if (!Hash::check($data['password_actual'], $user->password)) {
                    Notification::make()
                        ->title('La contraseña actual no es correcta.')
                        ->danger()
                        ->send();
                    return;
                }

                $user->update(['password' => $data['password_nueva']]);

                Notification::make()
                    ->title('Contraseña actualizada correctamente.')
                    ->success()
                    ->send();
            });
    }
}
