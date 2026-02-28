<?php

namespace Notificaciones\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Notificaciones\Models\NotificacionEtiqueta;
use Notificaciones\Models\NotificacionPlantilla;
use Notificaciones\Models\Notificacion;
use Notificaciones\Enums\NotificacionEstado;

class NotificacionesHelper
{
    /**
     * Get the value of a tag from the database based on context
     *
     * @param string $tagName The tag name to look up
     * @param array|null $context Array with table names as keys and IDs as values (e.g., ['clientes' => 1, 'facturas' => 5])
     * @param int|null $id Optional ID to override the context
     * @return mixed The field value, or null if not found
     */
    public static function getTagValue(string $tagName, ?array $context = null, ?int $id = null): mixed
    {
        // Look up the tag in the database
        $tag = NotificacionEtiqueta::where('tag_name', $tagName)->first();

        if (!$tag) {
            return null;
        }

        // Split the tag_value to get table and field
        $parts = explode('.', $tag->tag_value);

        if (count($parts) !== 2) {
            return null;
        }

        [$table, $field] = $parts;

        // Determine the ID to use
        $recordId = $id;

        // If no ID is provided, try to get it from context
        if ($recordId === null && $context !== null) {
            $recordId = $context[$table] ?? null;
        }

        // Special case: for "emisores" table, use the active emisor if no ID provided
        if ($recordId === null && $table === 'emisores') {
            $recordId = DB::table('emisores')->where('activo', true)->value('id');
        }

        if ($recordId === null) {
            return null;
        }

        // Query the database to get the value
        $record = DB::table($table)->where('id', $recordId)->first();

        if (!$record) {
            return null;
        }

        return $record->{$field} ?? null;
    }

    /**
     * Replace all tags in a text with their values
     *
     * @param string $text The text containing tags (e.g., "Hello {{nombre}}, your invoice {{numero}}")
     * @param array|null $context Array with table names as keys and IDs as values
     * @return string The text with all tags replaced
     */
    public static function replaceTags(string $text, ?array $context = null): string
    {
        // Find all tags in the format {{tag_name}}
        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);

        if (empty($matches[1])) {
            return $text;
        }

        // Replace each tag with its value
        foreach ($matches[1] as $tagName) {
            $value = self::getTagValue($tagName, $context);
            $text = str_replace("{{{$tagName}}}", $value ?? '', $text);
        }

        return $text;
    }

    /**
     * Queue an email for sending
     *
     * @param string $plantillaNombre The template name
     * @param string $emailDestinatario The recipient email
     * @param array $context Array with table names as keys and IDs as values for tag replacement
     * @param string|null $relacionadoTabla The related table name (for tracking)
     * @param int|null $relacionadoId The related record ID (for tracking)
     * @param Model|null $adjuntable Optional model to attach (Factura or Presupuesto)
     * @return Notificacion The queued notification
     * @throws InvalidArgumentException If template not found or required data missing
     */
    public static function queueEmail(
        string $plantillaNombre,
        string $emailDestinatario,
        array $context,
        ?string $relacionadoTabla = null,
        ?int $relacionadoId = null,
        ?Model $adjuntable = null
    ): Notificacion {
        // Validate email
        if (!filter_var($emailDestinatario, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email no válido: {$emailDestinatario}");
        }

        // Find template
        $plantilla = NotificacionPlantilla::where('nombre', $plantillaNombre)->first();

        if (!$plantilla) {
            throw new InvalidArgumentException("Plantilla no encontrada: {$plantillaNombre}");
        }

        // Replace tags in subject and body
        $asuntoProcesado = self::replaceTags($plantilla->asunto, $context);
        $cuerpoHtmlProcesado = self::replaceTags($plantilla->cuerpo_html, $context);

        // Add custom HTML if exists
        if ($plantilla->html_personalizado) {
            $htmlPersonalizado = self::replaceTags($plantilla->html_personalizado, $context);
            $cuerpoHtmlProcesado .= "\n\n" . $htmlPersonalizado;
        }

        // Process plain text body if exists
        $cuerpoTextoProcesado = null;
        if ($plantilla->cuerpo_texto) {
            $cuerpoTextoProcesado = self::replaceTags($plantilla->cuerpo_texto, $context);
        }

        // Auto-detect relacionado from context if not provided
        if ($relacionadoTabla === null && $relacionadoId === null && !empty($context)) {
            // Use the first entry in context as relacionado
            $relacionadoTabla = array_key_first($context);
            $relacionadoId = $context[$relacionadoTabla];
        }

        // Create notification record
        $data = [
            'notificacion_plantilla_id' => $plantilla->id,
            'email_destinatario' => $emailDestinatario,
            'asunto_procesado' => $asuntoProcesado,
            'cuerpo_html_procesado' => $cuerpoHtmlProcesado,
            'cuerpo_texto_procesado' => $cuerpoTextoProcesado,
            'fecha' => now(),
            'estado' => NotificacionEstado::EnCola,
            'relacionado_tabla' => $relacionadoTabla,
            'relacionado_id' => $relacionadoId,
        ];

        // Add attachable if provided
        if ($adjuntable) {
            $data['adjuntable_type'] = get_class($adjuntable);
            $data['adjuntable_id'] = $adjuntable->id;
        }

        return Notificacion::create($data);
    }
}
