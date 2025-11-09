<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturaLogController extends Controller
{
    public function index(Factura $factura)
    {
        // proteger con auth:sanctum en rutas
        return response()->json($factura->logs()->orderBy('id')->get());
    }

    public function csv(Factura $factura): StreamedResponse
    {
        $filename = 'factura_'.$factura->id.'_logs.csv';

        $columns = ['id','factura_id','user_id','evento','datos','ip','user_agent','prev_hash','hash','created_at'];

        return response()->streamDownload(function () use ($factura, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns, ';');

            $factura->logs()->orderBy('id')->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->factura_id,
                        $r->user_id,
                        $r->evento,
                        json_encode($r->datos, JSON_UNESCAPED_UNICODE),
                        $r->ip,
                        $r->user_agent,
                        $r->prev_hash,
                        $r->hash,
                        $r->created_at?->format('Y-m-d H:i:s'),
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}