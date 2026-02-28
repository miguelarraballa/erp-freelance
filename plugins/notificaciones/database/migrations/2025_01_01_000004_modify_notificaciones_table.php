<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            // Eliminar campo datos
            $table->dropColumn('datos');

            // Añadir campos necesarios para el email
            $table->string('email_destinatario')->after('notificacion_plantilla_id');
            $table->text('asunto_procesado')->after('email_destinatario');
            $table->longText('cuerpo_html_procesado')->after('asunto_procesado');
            $table->longText('cuerpo_texto_procesado')->nullable()->after('cuerpo_html_procesado');

            // Añadir relación polimórfica para adjuntos (facturas o presupuestos)
            $table->string('adjuntable_type')->nullable()->after('relacionado_id');
            $table->unsignedBigInteger('adjuntable_id')->nullable()->after('adjuntable_type');

            // Índice para adjuntos
            $table->index(['adjuntable_type', 'adjuntable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            // Restaurar campo datos
            $table->json('datos')->nullable()->after('notificacion_plantilla_id');

            // Eliminar campos añadidos
            $table->dropColumn([
                'email_destinatario',
                'asunto_procesado',
                'cuerpo_html_procesado',
                'cuerpo_texto_procesado',
                'adjuntable_type',
                'adjuntable_id',
            ]);

            // Eliminar índice
            $table->dropIndex(['adjuntable_type', 'adjuntable_id']);
        });
    }
};
