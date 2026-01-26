<?php

namespace Notificaciones\Enums;

enum NotificacionEstado: string
{
    case EnCola = 'en_cola';
    case Enviado = 'enviado';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::EnCola => 'En cola',
            self::Enviado => 'Enviado',
            self::Error => 'Error',
        };
    }
}
