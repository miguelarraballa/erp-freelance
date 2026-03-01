<?php

namespace PortalClientes\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailCambioVerificacion extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    public function __construct(
        public User $user,
        string $token,
    ) {
        $this->verificationUrl = url(route('portal.verify-email-change', ['token' => $token], false));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifica tu nuevo email - Portal Cliente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'portal-clientes::emails.verificar-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
