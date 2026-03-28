<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailMatchController extends Controller
{
    public function check(Request $request)
    {
        $apiKey = $request->header('X-N8N-API-KEY');
        $bearer = $request->bearerToken();

        // 1) X-N8N-API-KEY env fallback
        if ($apiKey && hash_equals((string) env('N8N_API_KEY', ''), (string) $apiKey)) {
            // authorized
        } elseif ($bearer) {
            // 2) Try Sanctum personal access token if available
            if (class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                if (! $tokenModel) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
                // token valid
            } else {
                // If Sanctum not installed, optionally allow bearer equal to env token
                if (! hash_equals((string) env('N8N_API_KEY', ''), (string) $bearer)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'email' => 'nullable|email',
            'subject' => 'required|string',
        ]);

        // 1) Buscar por email en campos habituales
        if (! empty($data['email'])) {
            $email = $data['email'];
            $cliente = Cliente::where('contacto_email', $email)
                ->orWhere('email_facturacion', $email)
                ->first();

            if ($cliente) {
                return response()->json([
                    'matched' => true,
                    'match_type' => 'email',
                    'client' => $this->formatClient($cliente),
                ]);
            }
        }

        // 2) Si no hay email, o no se encontró, buscar similitud en subject
        $subject = mb_strtolower($data['subject']);

        $candidates = Cliente::where('activo', 1)
            ->get(['id', 'nombre', 'razon_social', 'contacto_nombre', 'contacto_email', 'codigo_cliente']);

        $best = null;
        $bestScore = 0;

        foreach ($candidates as $c) {
            $name = mb_strtolower($c->nombre ?? '');
            $razon = mb_strtolower($c->razon_social ?? '');

            // substring match quick check
            if ($name && mb_stripos($subject, $name) !== false) {
                $score = 100;
            } elseif ($razon && mb_stripos($subject, $razon) !== false) {
                $score = 100;
            } else {
                // similar_text gives percentage similarity; compare with both fields
                $score = 0;
                if ($name) {
                    similar_text($subject, $name, $p);
                    $score = max($score, (int) $p);
                }
                if ($razon) {
                    similar_text($subject, $razon, $p2);
                    $score = max($score, (int) $p2);
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $c;
            }
        }

        // umbral configurable vía env
        $threshold = (int) env('N8N_SUBJECT_SIMILARITY_THRESHOLD', 55);

        if ($best && $bestScore >= $threshold) {
            $result = [
                'matched' => true,
                'match_type' => 'similarity',
                'score' => $bestScore,
                'client' => $this->formatClient($best),
            ];

            Log::channel('n8n')->info('email-check', [
                'request' => ['email' => $data['email'] ?? null, 'subject' => $data['subject']],
                'result' => $result,
            ]);

            return response()->json($result);
        }

        $result = ['matched' => false];
        Log::channel('n8n')->info('email-check', [
            'request' => ['email' => $data['email'] ?? null, 'subject' => $data['subject']],
            'result' => $result,
        ]);

        return response()->json($result);
    }

    protected function formatClient(Cliente $c)
    {
        return [
            'contacto_nombre' => $c->contacto_nombre,
            'contacto_email' => $c->contacto_email,
            'nombre' => $c->nombre,
            'razon_social' => $c->razon_social,
            'codigo_cliente' => $c->codigo_cliente,
        ];
    }
}
