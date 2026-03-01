<?php

namespace PortalClientes\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PortalEmailVerificationController
{
    public function verify(Request $request, string $token)
    {
        $hashedToken = hash('sha256', $token);

        $user = User::where('email_pending_token', $hashedToken)
            ->whereNotNull('email_pending')
            ->first();

        if (!$user) {
            return redirect()->route('filament.portal.pages.mi-perfil')
                ->with('error', 'El enlace de verificación no es válido.');
        }

        if ($user->email_pending_expires_at && $user->email_pending_expires_at->isPast()) {
            $user->update([
                'email_pending'            => null,
                'email_pending_token'      => null,
                'email_pending_expires_at' => null,
            ]);

            return redirect()->route('filament.portal.pages.mi-perfil')
                ->with('error', 'El enlace de verificación ha caducado. Solicita uno nuevo.');
        }

        $user->update([
            'email'                    => $user->email_pending,
            'email_pending'            => null,
            'email_pending_token'      => null,
            'email_pending_expires_at' => null,
        ]);

        return redirect()->route('filament.portal.pages.mi-perfil')
            ->with('success', 'Tu email ha sido actualizado correctamente.');
    }
}
