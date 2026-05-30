<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notificaciones_plantillas')) {
            return;
        }

        $now = now();

        foreach ($this->plantillas() as $plantilla) {
            DB::table('notificaciones_plantillas')
                ->updateOrInsert(
                    ['nombre' => $plantilla['nombre']],
                    array_merge($plantilla, ['created_at' => $now, 'updated_at' => $now])
                );
        }

        if (Schema::hasTable('notificaciones_etiquetas')) {
            foreach ($this->etiquetas() as $etiqueta) {
                DB::table('notificaciones_etiquetas')
                    ->updateOrInsert(
                        ['tag_name' => $etiqueta['tag_name']],
                        array_merge($etiqueta, ['created_at' => $now, 'updated_at' => $now])
                    );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notificaciones_plantillas')) {
            DB::table('notificaciones_plantillas')
                ->whereIn('nombre', ['renovacion_servidores_1mes', 'renovacion_servidores_15dias'])
                ->delete();
        }

        if (Schema::hasTable('notificaciones_etiquetas')) {
            DB::table('notificaciones_etiquetas')
                ->whereIn('tag_name', array_column($this->etiquetas(), 'tag_name'))
                ->delete();
        }
    }

    private function plantillas(): array
    {
        return [
            [
                'nombre'          => 'renovacion_servidores_1mes',
                'asunto'          => 'Renovación próxima: {{servidores_nombre}} (1 mes)',
                'cuerpo_html'     => '<p>Hola,</p>
<p>El servidor <strong>{{servidores_nombre}}</strong> del cliente <strong>{{clientes_nombre}}</strong> se renueva el <strong>{{servidores_fecha_renovacion}}</strong> (<strong>en aproximadamente 1 mes</strong>).</p>
<ul>
  <li><strong>URL:</strong> {{servidores_url}}</li>
  <li><strong>Dominio:</strong> {{servidores_dominio}}</li>
  <li><strong>Paquete:</strong> {{servidores_paquete}}</li>
  <li><strong>Precio:</strong> {{servidores_precio}} €</li>
</ul>
<p>Por favor, gestiona la renovación con tiempo suficiente para evitar interrupciones del servicio.</p>
<p>Saludos,<br>{{emisores_nombre}}</p>',
                'cuerpo_texto'    => "Hola,\n\nEl servidor {{servidores_nombre}} del cliente {{clientes_nombre}} se renueva el {{servidores_fecha_renovacion}} (en aproximadamente 1 mes).\n\nURL: {{servidores_url}}\nDominio: {{servidores_dominio}}\nPaquete: {{servidores_paquete}}\nPrecio: {{servidores_precio}} €\n\nGestiona la renovación con tiempo suficiente.\n\n{{emisores_nombre}}",
                'html_personalizado' => null,
            ],
            [
                'nombre'          => 'renovacion_servidores_15dias',
                'asunto'          => 'URGENTE: Renovación de {{servidores_nombre}} en 15 días',
                'cuerpo_html'     => '<p>Hola,</p>
<p>⚠️ El servidor <strong>{{servidores_nombre}}</strong> del cliente <strong>{{clientes_nombre}}</strong> se renueva el <strong>{{servidores_fecha_renovacion}}</strong> (<strong>quedan aproximadamente 15 días</strong>).</p>
<ul>
  <li><strong>URL:</strong> {{servidores_url}}</li>
  <li><strong>Dominio:</strong> {{servidores_dominio}}</li>
  <li><strong>Paquete:</strong> {{servidores_paquete}}</li>
  <li><strong>Precio:</strong> {{servidores_precio}} €</li>
</ul>
<p><strong>Acción requerida:</strong> gestiona la renovación de inmediato para evitar interrupciones del servicio.</p>
<p>Saludos,<br>{{emisores_nombre}}</p>',
                'cuerpo_texto'    => "Hola,\n\nURGENTE: El servidor {{servidores_nombre}} del cliente {{clientes_nombre}} se renueva el {{servidores_fecha_renovacion}} (quedan aproximadamente 15 días).\n\nURL: {{servidores_url}}\nDominio: {{servidores_dominio}}\nPaquete: {{servidores_paquete}}\nPrecio: {{servidores_precio}} €\n\nAcción requerida: gestiona la renovación de inmediato.\n\n{{emisores_nombre}}",
                'html_personalizado' => null,
            ],
        ];
    }

    private function etiquetas(): array
    {
        return [
            ['tag_name' => 'servidores_nombre',           'tag_value' => 'servidores.nombre'],
            ['tag_name' => 'servidores_url',              'tag_value' => 'servidores.url'],
            ['tag_name' => 'servidores_dominio',          'tag_value' => 'servidores.dominio'],
            ['tag_name' => 'servidores_paquete',          'tag_value' => 'servidores.paquete'],
            ['tag_name' => 'servidores_precio',           'tag_value' => 'servidores.precio'],
            ['tag_name' => 'servidores_fecha_renovacion', 'tag_value' => 'servidores.fecha_renovacion'],
        ];
    }
};
