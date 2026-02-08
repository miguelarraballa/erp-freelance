<?php

namespace Notificaciones\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Notificaciones\Models\Notificacion;

class NotificacionEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Notificacion $notificacion
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notificacion->asunto_procesado,
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $message = $this->subject($this->notificacion->asunto_procesado)
            ->html($this->notificacion->cuerpo_html_procesado);

        // Add text version if available
        if (!empty($this->notificacion->cuerpo_texto_procesado)) {
            $message->text(new \Illuminate\Support\HtmlString($this->notificacion->cuerpo_texto_procesado));
        }

        return $message;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // If there's an attachable model (Factura or Presupuesto), attach its PDF
        if ($this->notificacion->adjuntable) {
            $adjuntable = $this->notificacion->adjuntable;

            // Check if the model has a method to generate PDF
            if (method_exists($adjuntable, 'generarPdf')) {
                $pdfPath = $adjuntable->generarPdf();

                if ($pdfPath && file_exists($pdfPath)) {
                    $attachments[] = Attachment::fromPath($pdfPath);
                }
            }
            // Alternative: check for a pdf_path attribute
            elseif (isset($adjuntable->pdf_path) && file_exists($adjuntable->pdf_path)) {
                $attachments[] = Attachment::fromPath($adjuntable->pdf_path);
            }
        }

        return $attachments;
    }
}
