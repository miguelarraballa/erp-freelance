<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu nuevo email</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #0d9488; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .body { padding: 40px; color: #374151; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; background: #0d9488; color: #ffffff !important; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 16px; margin: 16px 0; }
        .footer { background: #f9fafb; padding: 24px 40px; text-align: center; color: #9ca3af; font-size: 13px; border-top: 1px solid #e5e7eb; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 4px; margin-top: 24px; font-size: 14px; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Portal Cliente</h1>
        </div>
        <div class="body">
            <p>Hola, <strong>{{ $user->name }}</strong>,</p>
            <p>Hemos recibido una solicitud para cambiar el email de acceso de tu cuenta en el <strong>Portal Cliente</strong>.</p>
            <p>Para confirmar el cambio, haz clic en el siguiente botón:</p>
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="btn">Verificar nuevo email</a>
            </p>
            <p>O copia y pega este enlace en tu navegador:</p>
            <p style="word-break: break-all; background: #f3f4f6; padding: 10px; border-radius: 4px; font-size: 13px;">
                {{ $verificationUrl }}
            </p>
            <div class="warning">
                <strong>Importante:</strong> Este enlace caducará en <strong>24 horas</strong>.
                Si no solicitaste este cambio, puedes ignorar este correo — tu email actual no será modificado.
            </div>
        </div>
        <div class="footer">
            Este correo fue enviado automáticamente. Por favor, no respondas a este mensaje.
        </div>
    </div>
</body>
</html>
