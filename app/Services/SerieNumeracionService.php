<?php

namespace App\Services;

use App\Models\Serie;

class SerieNumeracionService
{
    public static function renderNumeroCompleto(Serie $serie, int $secuencial): string
    {
        $pattern = (string) ($serie->codigo ?? 'NNNN');
        $codigoRender = preg_replace_callback('/N+/', function ($m) use ($secuencial) {
            $len = strlen($m[0]);
            return str_pad((string) $secuencial, $len, '0', STR_PAD_LEFT);
        }, $pattern);

        $prefijo = (string) ($serie->prefijo ?? '');
        $sufijo = (string) ($serie->sufijo ?? '');

        return $prefijo . $codigoRender . $sufijo;
    }
}
