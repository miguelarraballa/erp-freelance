<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Servidores\Models\Servidor;

class N8nServidorController extends Controller
{
    public function upsert(Request $request)
    {
        $this->authorizeRequest($request);

        $data = $request->validate([
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'codigo_cliente' => ['nullable', 'string', 'max:250'],
            'cliente_email' => ['nullable', 'email', 'max:250'],
            'dominio' => ['required', 'string', 'max:250'],
            'nombre' => ['nullable', 'string', 'max:250'],
            'url' => ['nullable', 'url', 'max:250'],
            'paquete' => ['nullable', 'string', 'max:250'],
            'precio' => ['nullable', 'numeric', 'min:0'],
            'fecha_alta' => ['nullable', 'date'],
            'fecha_renovacion' => ['nullable', 'date'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $cliente = $this->findCliente($data);

        if (! $cliente) {
            return response()->json([
                'error' => 'Cliente no encontrado',
            ], 422);
        }

        $dominio = mb_strtolower(trim($data['dominio']));
        $fechaAlta = isset($data['fecha_alta'])
            ? Carbon::parse($data['fecha_alta'])->toDateString()
            : now()->toDateString();

        $servidor = Servidor::firstOrNew(['dominio' => $dominio]);
        $created = ! $servidor->exists;

        $servidor->fill([
            'cliente_id' => $cliente->id,
            'nombre' => $data['nombre'] ?? $dominio,
            'url' => $data['url'] ?? "https://{$dominio}",
            'dominio' => $dominio,
            'fecha_alta' => $fechaAlta,
            'fecha_renovacion' => $data['fecha_renovacion'] ?? Carbon::parse($fechaAlta)->addYear()->toDateString(),
            'paquete' => $data['paquete'] ?? null,
            'precio' => $data['precio'] ?? 0,
            'activo' => $data['activo'] ?? true,
        ]);
        $servidor->save();

        return response()->json([
            'created' => $created,
            'servidor' => [
                'id' => $servidor->id,
                'cliente_id' => $servidor->cliente_id,
                'dominio' => $servidor->dominio,
                'paquete' => $servidor->paquete,
            ],
        ], $created ? 201 : 200);
    }

    private function authorizeRequest(Request $request): void
    {
        $apiKey = $request->header('X-N8N-API-KEY');
        $bearer = $request->bearerToken();
        $expected = (string) env('N8N_API_KEY', '');

        if ($expected !== '' && (
            ($apiKey && hash_equals($expected, (string) $apiKey)) ||
            ($bearer && hash_equals($expected, (string) $bearer))
        )) {
            return;
        }

        abort(response()->json(['error' => 'Unauthorized'], 401));
    }

    private function findCliente(array $data): ?Cliente
    {
        if (! empty($data['cliente_id'])) {
            return Cliente::find($data['cliente_id']);
        }

        if (! empty($data['codigo_cliente'])) {
            return Cliente::where('codigo_cliente', $data['codigo_cliente'])->first();
        }

        if (! empty($data['cliente_email'])) {
            return Cliente::where('contacto_email', $data['cliente_email'])
                ->orWhere('email_facturacion', $data['cliente_email'])
                ->first();
        }

        return null;
    }
}
