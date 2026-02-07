<?php

namespace Notificaciones\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Notificaciones\Models\NotificacionEtiqueta;

class NotificacionesEtiquetasSeeder extends Seeder
{
    /**
     * Tables to scan for fields
     */
    protected array $tables = [
        'clientes',
        'emisores',
        'facturas',
        'facturas_proveedores',
        'facturas_lineas',
        'presupuestos',
        'presupuestos_lineas',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->tables as $table) {
            // Check if table exists
            if (!Schema::hasTable($table)) {
                $this->command->warn("Table '{$table}' does not exist. Skipping...");
                continue;
            }

            // Get all columns for the table
            $columns = Schema::getColumnListing($table);

            foreach ($columns as $column) {
                // Create tag name by combining table and column
                $tagName = "{$table}_{$column}";
                $tagValue = "{$table}.{$column}";

                // Insert or update the tag
                NotificacionEtiqueta::updateOrCreate(
                    ['tag_name' => $tagName],
                    ['tag_value' => $tagValue]
                );
            }

            $columnCount = count($columns);
            $this->command->info("Processed {$columnCount} columns from table '{$table}'");
        }

        $totalTags = NotificacionEtiqueta::count();
        $this->command->info("Seeder completed! Total tags in database: {$totalTags}");
    }
}
