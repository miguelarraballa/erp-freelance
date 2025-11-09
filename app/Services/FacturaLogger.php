<?php
namespace App\Services;

use App\Models\Factura;
use App\Models\FacturaLog;

class FacturaLogger
{
    public static function log(Factura $f, string $evento, array $datos = [], ?int $userId = null, ?string $ip = null, ?string $ua = null): void
    {
        $prev = $f->logs()->orderByDesc('id')->first();
        $prevHash = $prev?->hash;

        $timestamp = now()->toIso8601String();
        $secret = config('app.key'); // o .env('LOG_CHAIN_SECRET')
        $toHash = ($prevHash ?? '') . '|' . $f->id . '|' . $evento . '|' . json_encode($datos, JSON_UNESCAPED_UNICODE) . '|' . $timestamp . '|' . $secret;
        $hash = hash('sha256', $toHash);

        FacturaLog::create([
            'factura_id' => $f->id,
            'user_id' => $userId,
            'evento' => $evento,
            'datos' => $datos,
            'ip' => $ip,
            'user_agent' => $ua,
            'prev_hash' => $prevHash,
            'hash' => $hash,
            'created_at' => $timestamp,
        ]);
    }
}