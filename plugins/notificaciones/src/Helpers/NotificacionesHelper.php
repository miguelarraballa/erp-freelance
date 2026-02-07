<?php

namespace Notificaciones\Helpers;

use Illuminate\Support\Facades\DB;
use Notificaciones\Models\NotificacionEtiqueta;

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
}
