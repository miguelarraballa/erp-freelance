<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class PagoJustificanteController extends Controller
{
    public function show(Pago $pago): Response
    {
        if (! $pago->justificante_path) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($pago->justificante_path)) {
            abort(404);
        }

        $path = $disk->path($pago->justificante_path);
        $name = basename($pago->justificante_path);

        return response()->download($path, $name);
    }
}
